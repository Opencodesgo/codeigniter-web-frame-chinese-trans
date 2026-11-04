<?php
/**
 * CodeIgniter，数据库，SQLite3，连接
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ErrorException;
use Exception;
use SQLite3;
use stdClass;

/**
 * Connection for SQLite3
 * SQLite3 连接。
 */
class Connection extends BaseConnection
{
	/**
	 * Database driver
	 * 数据库驱动
	 *
	 * @var string
	 */
	public $DBDriver = 'SQLite3';

	/**
	 * Identifier escape character
	 * 标识符转义字符
	 *
	 * @var string
	 */
	public $escapeChar = '`';

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
		if ($persistent && $this->DBDebug)
		{
			throw new DatabaseException('SQLite3 doesn\'t support persistent connections.');
		}
		try
		{
			if ($this->database !== ':memory:' && strpos($this->database, DIRECTORY_SEPARATOR) === false)
			{
				$this->database = WRITEPATH . $this->database;
			}

			return (! $this->password)
				? new SQLite3($this->database)
				: new SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);
		}
		catch (Exception $e)
		{
			throw new DatabaseException('SQLite3 error: ' . $e->getMessage());
		}
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

		$version = SQLite3::version();

		return $this->dataCache['version'] = $version['versionString'];
	}

	//--------------------------------------------------------------------

	/**
	 * Execute the query
	 * 执行语句
	 *
	 * @param string $sql
	 *
	 * @return mixed    \SQLite3Result object or bool
	 */
	public function execute(string $sql)
	{
		try
		{
			return $this->isWriteType($sql)
				? $this->connID->exec($sql)
				: $this->connID->query($sql);
		}
		catch (ErrorException $e)
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
	 * Returns the total number of rows affected by this query.
	 * 返回受此查询影响的行的总数
	 *
	 * @return integer
	 */
	public function affectedRows(): int
	{
		return $this->connID->changes();
	}

	//--------------------------------------------------------------------

	/**
	 * Platform-dependant string escape
	 * 与平台相关的字符串转义
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	protected function _escapeString(string $str): string
	{
		return $this->connID->escapeString($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 * 以平台相关的方式生成用于列出表的SQL
	 *
	 * @param boolean $prefixLimit
	 *
	 * @return string
	 */
	protected function _listTables(bool $prefixLimit = false): string
	{
		return 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\''
			   . ' AND "NAME" NOT LIKE \'sqlite!_%\' ESCAPE \'!\''
			   . (($prefixLimit !== false && $this->DBPrefix !== '')
				? ' AND "NAME" LIKE \'' . $this->escapeLikeString($this->DBPrefix) . '%\' ' . sprintf($this->likeEscapeStr,
					$this->likeEscapeChar)
				: '');
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
		return 'PRAGMA TABLE_INFO(' . $this->protectIdentifiers($table, true, null, false) . ')';
	}

	/**
	 * Fetch Field Names
	 * 获取字段名称
	 *
	 * @param string $table Table name
	 *
	 * @return array|false
	 * @throws DatabaseException
	 */
	public function getFieldNames(string $table)
	{
		// Is there a cached result?
		if (isset($this->dataCache['field_names'][$table]))
		{
			return $this->dataCache['field_names'][$table];
		}

		if (empty($this->connID))
		{
			$this->initialize();
		}

		$sql = $this->_listColumns($table);

		$query                                  = $this->query($sql);
		$this->dataCache['field_names'][$table] = [];

		foreach ($query->getResultArray() as $row)
		{
			// Do we know from where to get the column's name?
			// 我们知道从哪里得到列的名称吗？
			if (! isset($key))
			{
				if (isset($row['column_name']))
				{
					$key = 'column_name';
				}
				elseif (isset($row['COLUMN_NAME']))
				{
					$key = 'COLUMN_NAME';
				}
				elseif (isset($row['name']))
				{
					$key = 'name';
				}
				else
				{
					// We have no other choice but to just get the first element's key.
					// 我们别无选择，只能得到第一个元素的键。
					$key = key($row);
				}
			}

			$this->dataCache['field_names'][$table][] = $row[$key];
		}

		return $this->dataCache['field_names'][$table];
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
		if (($query = $this->query('PRAGMA TABLE_INFO(' . $this->protectIdentifiers($table, true, null,
					false) . ')')) === false)
		{
			throw new DatabaseException(lang('Database.failGetFieldData'));
		}
		$query = $query->getResultObject();

		if (empty($query))
		{
			return [];
		}
		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retVal[$i]              = new stdClass();
			$retVal[$i]->name        = $query[$i]->name;
			$retVal[$i]->type        = $query[$i]->type;
			$retVal[$i]->max_length  = null;
			$retVal[$i]->default     = $query[$i]->dflt_value;
			$retVal[$i]->primary_key = isset($query[$i]->pk) && (bool) $query[$i]->pk;
			$retVal[$i]->nullable    = isset($query[$i]->notnull) && ! (bool) $query[$i]->notnull;
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
	 */
	public function _indexData(string $table): array
	{
		// Get indexes
		// Don't use PRAGMA index_list, so we can preserve index order
		$sql = "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=" . $this->escape(strtolower($table));
		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException(lang('Database.failGetIndexData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj       = new stdClass();
			$obj->name = $row->name;

			// Get fields for index
			$obj->fields = [];
			if (($fields = $this->query('PRAGMA index_info(' . $this->escape(strtolower($row->name)) . ')')) === false)
			{
				throw new DatabaseException(lang('Database.failGetIndexData'));
			}
			$fields = $fields->getResultObject();

			foreach ($fields as $field)
			{
				$obj->fields[] = $field->name;
			}

			$retVal[$obj->name] = $obj;
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with Foreign key data
	 * 返回具有外键数据的对象数组
	 *
	 * @param  string $table
	 * @return stdClass[]
	 */
	public function _foreignKeyData(string $table): array
	{
		if ($this->supportsForeignKeys() !== true)
		{
			return [];
		}

		$tables = $this->listTables();

		if (empty($tables))
		{
			return [];
		}

		$retVal = [];

		foreach ($tables as $table)
		{
			$query = $this->query("PRAGMA foreign_key_list({$table})")->getResult();

			foreach ($query as $row)
			{
				$obj                     = new stdClass();
				$obj->constraint_name    = $row->from . ' to ' . $row->table . '.' . $row->to;
				$obj->table_name         = $table;
				$obj->foreign_table_name = $row->table;
				$obj->sequence           = $row->seq;

				$retVal[] = $obj;
			}
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
		return 'PRAGMA foreign_keys = OFF';
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
		return 'PRAGMA foreign_keys = ON';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last error code and message.
	 * 返回最后一个错误代码和消息。
	 *
	 * Must return an array with keys 'code' and 'message':
	 *
	 *  return ['code' => null, 'message' => null);
	 *
	 * @return array
	 */
	public function error(): array
	{
		return [
			'code'    => $this->connID->lastErrorCode(),
			'message' => $this->connID->lastErrorMsg(),
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
		return $this->connID->lastInsertRowID();
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
		return $this->connID->exec('BEGIN TRANSACTION');
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
		return $this->connID->exec('END TRANSACTION');
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
		return $this->connID->exec('ROLLBACK');
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 * 确定语句是否是写类型查询
	 *
	 * @return boolean
	 */
	public function isWriteType($sql): bool
	{
		return (bool) preg_match(
			'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i',
			$sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the current install supports Foreign Keys
	 * and has them enabled.
	 * 检查当前安装是否支持外键，并确认其已启用。
	 *
	 * @return boolean
	 */
	public function supportsForeignKeys(): bool
	{
		$result = $this->simpleQuery('PRAGMA foreign_keys');

		return (bool) $result;
	}

	//--------------------------------------------------------------------
}
