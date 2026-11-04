<?php
/**
 * CodeIgniter，会话，处理程序，Redis 处理器
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

use CodeIgniter\Session\Exceptions\SessionException;
use Config\App as AppConfig;
use Exception;
use Redis;
use RedisException;

/**
 * Session handler using Redis for persistence
 * 会话处理程序使用Redis持久化。
 */
class RedisHandler extends BaseHandler
{
	/**
	 * phpRedis instance
	 * phpRedis实例
	 *
	 * @var Redis|null
	 */
	protected $redis;

	/**
	 * Key prefix
	 * 键前缀
	 *
	 * @var string
	 */
	protected $keyPrefix = 'ci_session:';

	/**
	 * Lock key
	 * 锁密钥
	 *
	 * @var string|null
	 */
	protected $lockKey;

	/**
	 * Key exists flag
	 * 键存在标志
	 *
	 * @var boolean
	 */
	protected $keyExists = false;

	/**
	 * Number of seconds until the session ends.
	 * 会话结束前的秒数
	 *
	 * @var integer
	 */
	protected $sessionExpiration = 7200;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param AppConfig $config
	 * @param string    $ipAddress
	 *
	 * @throws Exception
	 */
	public function __construct(AppConfig $config, string $ipAddress)
	{
		parent::__construct($config, $ipAddress);

		if (empty($this->savePath))
		{
			throw SessionException::forEmptySavepath();
		}

		if (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->savePath, $matches))
		{
			// @phpstan-ignore-next-line
			isset($matches[3]) || $matches[3] = ''; // Just to avoid undefined index notices below

			$this->savePath = [
				'host'     => $matches[1],
				'port'     => empty($matches[2]) ? null : $matches[2],
				'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : null,
				'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : null,
				'timeout'  => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float) $match[1] : null,
			];

			preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->keyPrefix = $match[1];
		}
		else
		{
			throw SessionException::forInvalidSavePathFormat($this->savePath);
		}

		if ($this->matchIP === true)
		{
			$this->keyPrefix .= $this->ipAddress . ':';
		}

		$this->sessionExpiration = empty($config->sessionExpiration)
			? (int) ini_get('session.gc_maxlifetime')
			: (int) $config->sessionExpiration;
	}

	//--------------------------------------------------------------------

	/**
	 * Open
	 * 打开。
	 *
	 * Sanitizes save_path and initializes connection.
	 * Sanitizes save_path并初始化连接。
	 *
	 * @param  string $savePath Server path
	 * @param  string $name     Session cookie name, unused
	 * @return boolean
	 */
	public function open($savePath, $name): bool
	{
		if (empty($this->savePath))
		{
			return false;
		}

		$redis = new Redis();

		if (! $redis->connect($this->savePath['host'], $this->savePath['port'], $this->savePath['timeout']))
		{
			$this->logger->error('Session: Unable to connect to Redis with the configured settings.');
		}
		elseif (isset($this->savePath['password']) && ! $redis->auth($this->savePath['password']))
		{
			$this->logger->error('Session: Unable to authenticate to Redis instance.');
		}
		elseif (isset($this->savePath['database']) && ! $redis->select($this->savePath['database']))
		{
			$this->logger->error('Session: Unable to select Redis database with index ' . $this->savePath['database']);
		}
		else
		{
			$this->redis = $redis;
			return true;
		}

		return false;
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
	 * @return string	Serialized session data
	 */
	public function read($sessionID): string
	{
		if (isset($this->redis) && $this->lockSession($sessionID))
		{
			// Needed by write() to detect session_regenerate_id() calls
			if (is_null($this->sessionID)) // @phpstan-ignore-line
			{
				$this->sessionID = $sessionID;
			}

			$sessionData                               = $this->redis->get($this->keyPrefix . $sessionID);
			is_string($sessionData) ? $this->keyExists = true : $sessionData = '';

			$this->fingerprint = md5($sessionData);

			return $sessionData;
		}

		return '';
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
		if (! isset($this->redis))
		{
			return false;
		}

		// Was the ID regenerated?
		if ($sessionID !== $this->sessionID)
		{
			if (! $this->releaseLock() || ! $this->lockSession($sessionID))
			{
				return false;
			}

			$this->keyExists = false;
			$this->sessionID = $sessionID;
		}

		if (isset($this->lockKey))
		{
			$this->redis->expire($this->lockKey, 300);

			if ($this->fingerprint !== ($fingerprint = md5($sessionData)) || $this->keyExists === false)
			{
				if ($this->redis->set($this->keyPrefix . $sessionID, $sessionData, $this->sessionExpiration))
				{
					$this->fingerprint = $fingerprint;
					$this->keyExists   = true;
					return true;
				}

				return false;
			}

			return $this->redis->expire($this->keyPrefix . $sessionID, $this->sessionExpiration);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Close
	 * 关闭。
	 *
	 * Releases locks and closes connection.
	 * 释放锁并关闭连接。
	 *
	 * @return boolean
	 */
	public function close(): bool
	{
		if (isset($this->redis))
		{
			try
			{
				$pingReply = $this->redis->ping();
				// @phpstan-ignore-next-line
				if (($pingReply === true) || ($pingReply === '+PONG'))
				{
					isset($this->lockKey) && $this->redis->del($this->lockKey);

					if (! $this->redis->close())
					{
						return false;
					}
				}
			}
			catch (RedisException $e)
			{
				$this->logger->error('Session: Got RedisException on close(): ' . $e->getMessage());
			}

			$this->redis = null;

			return true;
		}

		return true;
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
		if (isset($this->redis, $this->lockKey))
		{
			if (($result = $this->redis->del($this->keyPrefix . $sessionID)) !== 1)
			{
				$this->logger->debug('Session: Redis::del() expected to return 1, got ' . var_export($result, true) . ' instead.');
			}

			return $this->destroyCookie();
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Garbage Collector
	 * 垃圾回收器。
	 *
	 * Deletes expired sessions
	 * 删除过期会话。
	 *
	 * @param  integer $maxlifetime Maximum lifetime of sessions
	 * @return boolean
	 */
	public function gc($maxlifetime): bool
	{
		// Not necessary, Redis takes care of that.
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get lock
	 * 获取锁。
	 *
	 * Acquires an (emulated) lock.
	 * 获取（模拟）锁。
	 *
	 * @param string $sessionID Session ID
	 *
	 * @return boolean
	 */
	protected function lockSession(string $sessionID): bool
	{
		// PHP 7 reuses the SessionHandler object on regeneration,
		// so we need to check here if the lock key is for the
		// correct session ID.
		if ($this->lockKey === $this->keyPrefix . $sessionID . ':lock')
		{
			return $this->redis->expire($this->lockKey, 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lockKey = $this->keyPrefix . $sessionID . ':lock';
		$attempt = 0;

		do
		{
			if (($ttl = $this->redis->ttl($lockKey)) > 0)
			{
				sleep(1);
				continue;
			}

			if (! $this->redis->setex($lockKey, 300, (string) time()))
			{
				$this->logger->error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionID);
				return false;
			}

			$this->lockKey = $lockKey;
			break;
		}
		while (++ $attempt < 30);

		if ($attempt === 30)
		{
			log_message('error', 'Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionID . ' after 30 attempts, aborting.');
			return false;
		}

		if ($ttl === -1)
		{
			log_message('debug', 'Session: Lock for ' . $this->keyPrefix . $sessionID . ' had no TTL, overriding.');
		}

		$this->lock = true;
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Release lock
	 * 释放锁。
	 *
	 * Releases a previously acquired lock
	 * 释放先前获得的锁。
	 *
	 * @return boolean
	 */
	protected function releaseLock(): bool
	{
		if (isset($this->redis, $this->lockKey) && $this->lock)
		{
			if (! $this->redis->del($this->lockKey))
			{
				$this->logger->error('Session: Error while trying to free lock for ' . $this->lockKey);
				return false;
			}

			$this->lockKey = null;
			$this->lock    = false;
		}

		return true;
	}

	//--------------------------------------------------------------------
}
