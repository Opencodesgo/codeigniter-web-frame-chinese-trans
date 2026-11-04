<?php
/**
 * CodeIgniter，数据库，MySQLi，连接
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use LogicException;
use MySQLi;
use mysqli_sql_exception;
use stdClass;
use Throwable;

/**
 * Connection for MySQLi
 * MySQLi连接。
 */
class Connection extends BaseConnection
{
	/**
	 * Database driver
	 * 数据库驱动
	 *
	 * @var string
	 */
	public $DBDriver = 'MySQLi';

	/**
	 * DELETE hack flag
	 * 删除黑客标志
	 *
	 * Whether to use the MySQL "delete hack" which allows the number
	 * of affected rows to be shown. Uses a preg_replace when enabled,
	 * adding a bit more processing to all queries.
	 * 是否使用 MySQL 的“删除技巧”，该技巧可显示受影响的行数。
	 * 启用时会使用 preg_replace，从而在所有查询中增加一些处理开销。
	 *
	 * @var boolean
	 */
	public $deleteHack = true;

	// --------------------------------------------------------------------

	/**
	 * Identifier escape character
	 * 标识符转义字符
	 *
	 * @var string
	 */
	public $escapeChar = '`';

	// --------------------------------------------------------------------

	/**
	 * MySQLi object
	 * MySQLi对象。
	 *
	 * Has to be preserved without being assigned to $conn_id.
	 * 必须保留而不被分配给$conn_id。
	 *
	 * @var MySQLi
	 */
	public $mysqli;

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 * 连接数据库
	 *
	 * @param boolean $persistent
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function connect(bool $persistent = false)
	{
		// Do we have a socket path?
		if ($this->hostname[0] === '/')
		{
			$hostname = null;
			$port     = null;
			$socket   = $this->hostname;
		}
		else
		{
			$hostname = ($persistent === true) ? 'p:' . $this->hostname : $this->hostname;
			$port     = empty($this->port) ? null : $this->port;
			$socket   = '';
		}

		$clientFlags  = ($this->compress === true) ? MYSQLI_CLIENT_COMPRESS : 0;
		$this->mysqli = mysqli_init();

		mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);

		$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

		if (isset($this->strictOn))
		{
			if ($this->strictOn)
			{
				$this->mysqli->options(MYSQLI_INIT_COMMAND,
					'SET SESSION sql_mode = CONCAT(@@sql_mode, ",", "STRICT_ALL_TABLES")');
			}
			else
			{
				$this->mysqli->options(MYSQLI_INIT_COMMAND, 'SET SESSION sql_mode =
						REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
												@@sql_mode,
												"STRICT_ALL_TABLES,", ""),
											",STRICT_ALL_TABLES", ""),
										"STRICT_ALL_TABLES", ""),
									"STRICT_TRANS_TABLES,", ""),
								",STRICT_TRANS_TABLES", ""),
							"STRICT_TRANS_TABLES", "")'
				);
			}
		}

		if (is_array($this->encrypt))
		{
			$ssl = [];

			empty($this->encrypt['ssl_key'])    || $ssl['key']    = $this->encrypt['ssl_key'];
			empty($this->encrypt['ssl_cert'])   || $ssl['cert']   = $this->encrypt['ssl_cert'];
			empty($this->encrypt['ssl_ca'])     || $ssl['ca']     = $this->encrypt['ssl_ca'];
			empty($this->encrypt['ssl_capath']) || $ssl['capath'] = $this->encrypt['ssl_capath'];
			empty($this->encrypt['ssl_cipher']) || $ssl['cipher'] = $this->encrypt['ssl_cipher'];

			if (! empty($ssl))
			{
				if (isset($this->encrypt['ssl_verify']))
				{
					if ($this->encrypt['ssl_verify'])
					{
						defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT') &&
						$this->mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, 1);
					}
					// Apparently (when it exists), setting MYSQLI_OPT_SSL_VERIFY_SERVER_CERT
					// to FALSE didn't do anything, so PHP 5.6.16 introduced yet another
					// constant ...
					//
					// https://secure.php.net/ChangeLog-5.php#5.6.16
					// https://bugs.php.net/bug.php?id=68344
					elseif (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT') && version_compare($this->mysqli->client_info, '5.6', '>='))
					{
						$clientFlags += MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
					}
				}

				$clientFlags += MYSQLI_CLIENT_SSL;
				$this->mysqli->ssl_set(
					$ssl['key'] ?? null, $ssl['cert'] ?? null, $ssl['ca'] ?? null,
					$ssl['capath'] ?? null, $ssl['cipher'] ?? null
				);
			}
		}

		try
		{
			if ($this->mysqli->real_connect($hostname, $this->username, $this->password,
				$this->database, $port, $socket, $clientFlags)
			)
			{
				// Prior to version 5.7.3, MySQL silently downgrades to an unencrypted connection if SSL setup fails
				if (($clientFlags & MYSQLI_CLIENT_SSL) && version_compare($this->mysqli->client_info, '5.7.3', '<=')
					&& empty($this->mysqli->query("SHOW STATUS LIKE 'ssl_cipher'")
										  ->fetch_object()->Value)
				)
				{
					$this->mysqli->close();
					$message = 'MySQLi was configured for an SSL connection, but got an unencrypted connection instead!';
					log_message('error', $message);

					if ($this->DBDebug)
					{
						throw new DatabaseException($message);
					}

					return false;
				}

				if (! $this->mysqli->set_charset($this->charset))
				{
					log_message('error',
						"Database: Unable to set the configured connection charset ('{$this->charset}').");
					$this->mysqli->close();

					if ($this->DBDebug)
					{
						throw new DatabaseException('Unable to set client connection character set: ' . $this->charset);
					}

					return false;
				}

				return $this->mysqli;
			}
		}
		catch (Throwable $e)
		{
			// Clean sensitive information from errors.
			$msg = $e->getMessage();

			$msg = str_replace($this->username, '****', $msg);
			$msg = str_replace($this->password, '****', $msg);

			throw new DatabaseException($msg, $e->getCode(), $e);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 * 如果一段时间内未发送查询，且该时间超过服务器的空闲超时时间，则保持或建立连接。
	 *
	 * @return void
	 */
	public function reconnect()
	{
		$this->close();
		$this->initialize();
	}

	//--------------------------------------------------------------------

	/**
	 * Close the database connection.
	 * 关闭数据库连接
	 *
	 * @return void
	 */
	protected function _close()
	{
		$this->connID->close();
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 * 选择要使用的特定数据库表
	 *
	 * @param string $databaseName
	 *
	 * @return boolean
	 */
	public function setDatabase(string $databaseName): bool
	{
		if ($databaseName === '')
		{
			$databaseName = $this->database;
		}

		if (empty($this->connID))
		{
			$this->initialize();
		}

		if ($this->connID->select_db($databaseName))
		{
			$this->database = $databaseName;

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 * 返回包含正在使用的数据库版本的字符串
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		if (isset($this->dataCache['version']))
		{
			return $this->dataCache['version'];
		}

		if (empty($this->mysqli))
		{
			$this->initialize();
		}

		return $this->dataCache['version'] = $this->mysqli->server_info;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 * 对数据库执行查询
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function execute(string $sql)
	{
		while ($this->connID->more_results())
		{
			$this->connID->next_result();
			if ($res = $this->connID->store_result())
			{
				$res->free();
			}
		}
		try
		{
			return $this->connID->query($this->prepQuery($sql));
		}
		catch (mysqli_sql_exception $e)
		{
			log_message('error', $e);
			if ($this->DBDebug)
			{
				throw $e;
			}
		}
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Prep the query
	 * 准备查询。
	 *
	 * If needed, each database adapter can prep the query string
	 * 如果需要，每个数据库适配器都可以准备查询字符串。
	 *
	 * @param string $sql an SQL query
	 *
	 * @return string
	 */
	protected function prepQuery(string $sql): string
	{
		// mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This hack
		// modifies the query so that it a proper number of affected rows is returned.
		if ($this->deleteHack === true && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
		{
			return trim($sql) . ' WHERE 1=1';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of rows affected by this query.
	 * 返回受此查询影响的行的总数
	 *
	 * @return integer
	 */
	public function affectedRows(): int
	{
		return $this->connID->affected_rows ?? 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Platform-dependant string escape
	 * 与平台相关的字符串转义
	 *
	 * @param  string $str
	 * @return string
	 */
	protected function _escapeString(string $str): string
	{
		if (! $this->connID)
		{
			$this->initialize();
		}

		return $this->connID->real_escape_string($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Escape Like String Direct
	 * There are a few instances where MySQLi queries cannot take the
	 * additional "ESCAPE x" parameter for specifying the escape character
	 * in "LIKE" strings, and this handles those directly with a backslash.
	 * 像字符串一样直接转义。
	 *
	 * @param  string|string[] $str Input string
	 * @return string|string[]
	 */
	public function escapeLikeStringDirect($str)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escapeLikeStringDirect($val);
			}

			return $str;
		}

		$str = $this->_escapeString($str);

		// Escape LIKE condition wildcards
		return str_replace([
			$this->likeEscapeChar,
			'%',
			'_',
		], [
			'\\' . $this->likeEscapeChar,
			'\\' . '%',
			'\\' . '_',
		], $str
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 * Uses escapeLikeStringDirect().
	 * 以平台相关的方式生成用于列出表的SQL
	 *
	 * @param boolean $prefixLimit
	 *
	 * @return string
	 */
	protected function _listTables(bool $prefixLimit = false): string
	{
		$sql = 'SHOW TABLES FROM ' . $this->escapeIdentifiers($this->database);

		if ($prefixLimit !== false && $this->DBPrefix !== '')
		{
			return $sql . " LIKE '" . $this->escapeLikeStringDirect($this->DBPrefix) . "%'";
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 * 生成特定于平台的查询字符串，以便获取列名。
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _listColumns(string $table = ''): string
	{
		return 'SHOW COLUMNS FROM ' . $this->protectIdentifiers($table, true, null, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with field data
	 * 返回一个包含字段数据的对象数组
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 */
	public function _fieldData(string $table): array
	{
		$table = $this->protectIdentifiers($table, true, null, false);

		if (($query = $this->query('SHOW COLUMNS FROM ' . $table)) === false)
		{
			throw new DatabaseException(lang('Database.failGetFieldData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retVal[$i]       = new stdClass();
			$retVal[$i]->name = $query[$i]->Field;

			sscanf($query[$i]->Type, '%[a-z](%d)', $retVal[$i]->type, $retVal[$i]->max_length);

			$retVal[$i]->nullable    = $query[$i]->Null === 'YES';
			$retVal[$i]->default     = $query[$i]->Default;
			$retVal[$i]->primary_key = (int) ($query[$i]->Key === 'PRI');
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with index data
	 * 返回一个包含索引数据的对象数组
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 * @throws LogicException
	 */
	public function _indexData(string $table): array
	{
		$table = $this->protectIdentifiers($table, true, null, false);

		if (($query = $this->query('SHOW INDEX FROM ' . $table)) === false)
		{
			throw new DatabaseException(lang('Database.failGetIndexData'));
		}

		if (! $indexes = $query->getResultArray())
		{
			return [];
		}

		$keys = [];

		foreach ($indexes as $index)
		{
			if (empty($keys[$index['Key_name']]))
			{
				$keys[$index['Key_name']]       = new stdClass();
				$keys[$index['Key_name']]->name = $index['Key_name'];

				if ($index['Key_name'] === 'PRIMARY')
				{
					$type = 'PRIMARY';
				}
				elseif ($index['Index_type'] === 'FULLTEXT')
				{
					$type = 'FULLTEXT';
				}
				elseif ($index['Non_unique'])
				{
					if ($index['Index_type'] === 'SPATIAL')
					{
						$type = 'SPATIAL';
					}
					else
					{
						$type = 'INDEX';
					}
				}
				else
				{
					$type = 'UNIQUE';
				}

				$keys[$index['Key_name']]->type = $type;
			}

			$keys[$index['Key_name']]->fields[] = $index['Column_name'];
		}

		return $keys;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with Foreign key data
	 * 返回具有外键数据的对象数组
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 */
	public function _foreignKeyData(string $table): array
	{
		$sql = '
                    SELECT
                        tc.CONSTRAINT_NAME,
                        tc.TABLE_NAME,
                        kcu.COLUMN_NAME,
                        rc.REFERENCED_TABLE_NAME,
                        kcu.REFERENCED_COLUMN_NAME
                    FROM information_schema.TABLE_CONSTRAINTS AS tc
                    INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS AS rc
                        ON tc.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                    INNER JOIN information_schema.KEY_COLUMN_USAGE AS kcu
                        ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                    WHERE
                        tc.CONSTRAINT_TYPE = ' . $this->escape('FOREIGN KEY') . ' AND
                        tc.TABLE_SCHEMA = ' . $this->escape($this->database) . ' AND
                        tc.TABLE_NAME = ' . $this->escape($table);

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException(lang('Database.failGetForeignKeyData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj                      = new stdClass();
			$obj->constraint_name     = $row->CONSTRAINT_NAME;
			$obj->table_name          = $row->TABLE_NAME;
			$obj->column_name         = $row->COLUMN_NAME;
			$obj->foreign_table_name  = $row->REFERENCED_TABLE_NAME;
			$obj->foreign_column_name = $row->REFERENCED_COLUMN_NAME;

			$retVal[] = $obj;
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns platform-specific SQL to disable foreign key checks.
	 * 返回特定于平台的SQL以禁用外键检查
	 *
	 * @return string
	 */
	protected function _disableForeignKeyChecks()
	{
		return 'SET FOREIGN_KEY_CHECKS=0';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns platform-specific SQL to enable foreign key checks.
	 * 返回特定于平台的SQL以启用外键检查
	 *
	 * @return string
	 */
	protected function _enableForeignKeyChecks()
	{
		return 'SET FOREIGN_KEY_CHECKS=1';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last error code and message.
	 * 返回最后一个错误代码和消息。
	 *
	 * Must return an array with keys 'code' and 'message':
	 * 必须返回一个键为‘code’和‘message’的数组：
	 *
	 *  return ['code' => null, 'message' => null);
	 *
	 * @return array
	 */
	public function error(): array
	{
		if (! empty($this->mysqli->connect_errno))
		{
			return [
				'code'    => $this->mysqli->connect_errno,
				'message' => $this->mysqli->connect_error,
			];
		}

		return [
			'code'    => $this->connID->errno,
			'message' => $this->connID->error,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 * 插入ID
	 *
	 * @return integer
	 */
	public function insertID(): int
	{
		return $this->connID->insert_id;
	}

	//--------------------------------------------------------------------

	/**
	 * Begin Transaction
	 * 开始事务
	 *
	 * @return boolean
	 */
	protected function _transBegin(): bool
	{
		$this->connID->autocommit(false);

		return $this->connID->begin_transaction();
	}

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 * 提交事务
	 *
	 * @return boolean
	 */
	protected function _transCommit(): bool
	{
		if ($this->connID->commit())
		{
			$this->connID->autocommit(true);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 * 回滚事务
	 *
	 * @return boolean
	 */
	protected function _transRollback(): bool
	{
		if ($this->connID->rollback())
		{
			$this->connID->autocommit(true);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------
}
