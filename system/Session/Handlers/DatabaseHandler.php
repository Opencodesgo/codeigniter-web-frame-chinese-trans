<?php
/**
 * CodeIgniter，会话，处理程序，数据库处理器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Config\Database;
use Exception;

/**
 * Session handler using current Database for storage
 * 会话处理程序使用当前数据库进行存储。
 */
class DatabaseHandler extends BaseHandler
{
	/**
	 * The database group to use for storage.
	 * 要用于存储的数据库组
	 *
	 * @var string
	 */
	protected $DBGroup;

	/**
	 * The name of the table to store session info.
	 * 用于存储会话信息的表名
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The DB Connection instance.
	 * DB Connection实例
	 *
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * The database type, for locking purposes.
	 * 数据库类型，用于锁定目的。
	 *
	 * @var string
	 */
	protected $platform;

	/**
	 * Row exists flag
	 * 行存在标志
	 *
	 * @var boolean
	 */
	protected $rowExists = false;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param AppConfig $config
	 * @param string    $ipAddress
	 */
	public function __construct(AppConfig $config, string $ipAddress)
	{
		parent::__construct($config, $ipAddress);

		// Determine Table
		$this->table = $config->sessionSavePath;

		if (empty($this->table))
		{
			throw SessionException::forMissingDatabaseTable();
		}

		// Get DB Connection
		// @phpstan-ignore-next-line
		$this->DBGroup = $config->sessionDBGroup ?? config(Database::class)->defaultGroup;

		$this->db = Database::connect($this->DBGroup);

		// Determine Database type
		$driver = strtolower(get_class($this->db));
		if (strpos($driver, 'mysql') !== false)
		{
			$this->platform = 'mysql';
		}
		elseif (strpos($driver, 'postgre') !== false)
		{
			$this->platform = 'postgre';
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Open
	 * 打开。
	 *
	 * Ensures we have an initialized database connection.
	 * 确保我们有一个初始化的数据库连接。
	 *
	 * @param string $savePath Path to session files' directory
	 * @param string $name     Session cookie name
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function open($savePath, $name): bool
	{
		if (empty($this->db->connID))
		{
			$this->db->initialize();
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Read
	 * 读取。
	 *
	 * Reads session data and acquires a lock
	 * 读取会话数据并获取锁。
	 *
	 * @param string $sessionID Session ID
	 *
	 * @return string    Serialized session data
	 */
	public function read($sessionID): string
	{
		if ($this->lockSession($sessionID) === false)
		{
			$this->fingerprint = md5('');
			return '';
		}

		// Needed by write() to detect session_regenerate_id() calls
		if (is_null($this->sessionID)) // @phpstan-ignore-line
		{
			$this->sessionID = $sessionID;
		}

		$builder = $this->db->table($this->table)
				->select('data')
				->where('id', $sessionID);

		if ($this->matchIP)
		{
			$builder = $builder->where('ip_address', $this->ipAddress);
		}

		$result = $builder->get()->getRow();

		if ($result === null)
		{
			// PHP7 will reuse the same SessionHandler object after
			// ID regeneration, so we need to explicitly set this to
			// FALSE instead of relying on the default ...
			$this->rowExists   = false;
			$this->fingerprint = md5('');

			return '';
		}

		// PostgreSQL's variant of a BLOB datatype is Bytea, which is a
		// PITA to work with, so we use base64-encoded data in a TEXT
		// field instead.
		if (is_bool($result))
		{
			$result = '';
		}
		else
		{
			$result = ($this->platform === 'postgre') ? base64_decode(rtrim($result->data), true) : $result->data;
		}

		$this->fingerprint = md5($result);
		$this->rowExists   = true;

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Write
	 * 写入。
	 *
	 * Writes (create / update) session data
	 *
	 * @param string $sessionID   Session ID
	 * @param string $sessionData Serialized session data
	 *
	 * @return boolean
	 */
	public function write($sessionID, $sessionData): bool
	{
		if ($this->lock === false)
		{
			return $this->fail();
		}

		// Was the ID regenerated?
		if ($sessionID !== $this->sessionID)
		{
			$this->rowExists = false;
			$this->sessionID = $sessionID;
		}

		if ($this->rowExists === false)
		{
			$insertData = [
				'id'         => $sessionID,
				'ip_address' => $this->ipAddress,
				'timestamp'  => time(),
				'data'       => $this->platform === 'postgre' ? base64_encode($sessionData) : $sessionData,
			];

			if (! $this->db->table($this->table)->insert($insertData))
			{
				return $this->fail();
			}

			$this->fingerprint = md5($sessionData);
			$this->rowExists   = true;

			return true;
		}

		$builder = $this->db->table($this->table)->where('id', $sessionID);

		if ($this->matchIP)
		{
			$builder = $builder->where('ip_address', $this->ipAddress);
		}

		$updateData = [
			'timestamp' => time(),
		];

		if ($this->fingerprint !== md5($sessionData))
		{
			$updateData['data'] = ($this->platform === 'postgre') ? base64_encode($sessionData) : $sessionData;
		}

		if (! $builder->update($updateData))
		{
			return $this->fail();
		}

		$this->fingerprint = md5($sessionData);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Close
	 * 关闭。
	 *
	 * Releases locks and closes file descriptor.
	 * 释放锁并关闭文件描述符。
	 *
	 * @return boolean
	 */
	public function close(): bool
	{
		return ($this->lock && ! $this->releaseLock()) ? $this->fail() : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Destroy
	 * 销毁。
	 *
	 * Destroys the current session.
	 * 销毁当前会话。
	 *
	 * @param string $sessionID
	 *
	 * @return boolean
	 */
	public function destroy($sessionID): bool
	{
		if ($this->lock)
		{
			$builder = $this->db->table($this->table)->where('id', $sessionID);

			if ($this->matchIP)
			{
				$builder = $builder->where('ip_address', $this->ipAddress);
			}

			if (! $builder->delete())
			{
				return $this->fail();
			}
		}

		if ($this->close())
		{
			$this->destroyCookie();

			return true;
		}

		return $this->fail();
	}

	//--------------------------------------------------------------------

	/**
	 * Garbage Collector
	 * 垃圾回收器。
	 *
	 * Deletes expired sessions
	 *
	 * @param integer $maxlifetime Maximum lifetime of sessions
	 *
	 * @return boolean
	 */
	public function gc($maxlifetime): bool
	{
		return ($this->db->table($this->table)->delete('timestamp < ' . (time() - $maxlifetime))) ? true : $this->fail();
	}

	//--------------------------------------------------------------------

	/**
	 * Lock the session.
	 * 锁定会话
	 *
	 * @param  string $sessionID
	 * @return boolean
	 */
	protected function lockSession(string $sessionID): bool
	{
		if ($this->platform === 'mysql')
		{
			$arg = md5($sessionID . ($this->matchIP ? '_' . $this->ipAddress : ''));
			if ($this->db->query("SELECT GET_LOCK('{$arg}', 300) AS ci_session_lock")->getRow()->ci_session_lock)
			{
				$this->lock = $arg;
				return true;
			}

			return $this->fail();
		}

		if ($this->platform === 'postgre')
		{
			$arg = "hashtext('{$sessionID}')" . ($this->matchIP ? ", hashtext('{$this->ipAddress}')" : '');
			if ($this->db->simpleQuery("SELECT pg_advisory_lock({$arg})"))
			{
				$this->lock = $arg;
				return true;
			}

			return $this->fail();
		}

		// Unsupported DB? Let the parent handle the simplified version.
		return parent::lockSession($sessionID);
	}

	//--------------------------------------------------------------------

	/**
	 * Releases the lock, if any.
	 * 释放锁（如果有的话）
	 *
	 * @return boolean
	 */
	protected function releaseLock(): bool
	{
		if (! $this->lock)
		{
			return true;
		}

		if ($this->platform === 'mysql')
		{
			if ($this->db->query("SELECT RELEASE_LOCK('{$this->lock}') AS ci_session_lock")->getRow()->ci_session_lock)
			{
				$this->lock = false;
				return true;
			}

			return $this->fail();
		}

		if ($this->platform === 'postgre')
		{
			if ($this->db->simpleQuery("SELECT pg_advisory_unlock({$this->lock})"))
			{
				$this->lock = false;
				return true;
			}

			return $this->fail();
		}

		// Unsupported DB? Let the parent handle the simple version.
		// 不受支持的数据库?让父节点处理简单版本。
		return parent::releaseLock();
	}

	//--------------------------------------------------------------------
}
