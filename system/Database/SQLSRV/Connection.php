<?php
/**
 * CodeIgniter，数据库，SQLSRV，连接
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLSRV;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Exception;
use stdClass;

/**
 * Connection for SQLSRV
 * SQLSRV 连接。
 */
class Connection extends BaseConnection
{
	/**
	 * Database driver
	 * 数据库驱动
	 *
	 * @var string
	 */
	public $DBDriver = 'SQLSRV';

	/**
	 * Database name
	 * 数据库名称
	 *
	 * @var string
	 */
	public $database;

	/**
	 * Scrollable flag
	 * 可滚动的标记。
	 *
	 * Determines what cursor type to use when executing queries.
	 * 确定在执行查询时要使用的游标类型。
	 *
	 * FALSE or SQLSRV_CURSOR_FORWARD would increase performance,
	 * but would disable num_rows() (and possibly insert_id())
	 *
	 * @var mixed
	 */
	public $scrollable;

	/**
	 * Identifier escape character
	 * 标识符转义字符
	 *
	 * @var string
	 */
	public $escapeChar = '"';

	/**
	 * Database schema
	 * 数据库模式
	 *
	 * @var string
	 */
	public $schema = 'dbo';

	/**
	 * Quoted identifier flag
	 * 引用标识符标志。
	 *
	 * Whether to use SQL-92 standard quoted identifier
	 * (double quotes) or brackets for identifier escaping.
	 * 使用 SQL-92 标准引号（双引号）还是用括号来转义标识符。
	 *
	 * @var boolean
	 */
	protected $_quoted_identifier = true;

	/**
	 * List of reserved identifiers
	 * 保留标识符列表。
	 *
	 * Identifiers that must NOT be escaped.
	 * 不能转义的标识符。
	 *
	 * @var string[]
	 */
	protected $_reserved_identifiers = ['*'];

	//--------------------------------------------------------------------

	/**
	 * Class constructor
	 * 类构造器
	 *
	 * @param  array $params
	 * @return void
	 */
	public function __construct($params)
	{
		parent::__construct($params);

		// This is only supported as of SQLSRV 3.0
		if ($this->scrollable === null)
		{
			$this->scrollable = defined('SQLSRV_CURSOR_CLIENT_BUFFERED') ? SQLSRV_CURSOR_CLIENT_BUFFERED : false;
		}
	}

	/**
	 * Connect to the database.
	 * 连接数据库
	 *
	 * @param  boolean $persistent
	 * @return mixed
	 */
	public function connect(bool $persistent = false)
	{
		$charset = in_array(strtolower($this->charset), ['utf-8', 'utf8'], true) ? 'UTF-8' : SQLSRV_ENC_CHAR;

		$connection = [
			'UID'                  => empty($this->username) ? '' : $this->username,
			'PWD'                  => empty($this->password) ? '' : $this->password,
			'Database'             => $this->database,
			'ConnectionPooling'    => ($persistent === true) ? 1 : 0,
			'CharacterSet'         => $charset,
			'Encrypt'              => ($this->encrypt === true) ? 1 : 0,
			'ReturnDatesAsStrings' => 1,
		];

		// If the username and password are both empty, assume this is a
		// 'Windows Authentication Mode' connection.
		if (empty($connection['UID']) && empty($connection['PWD']))
		{
			unset($connection['UID'], $connection['PWD']);
		}

		if (false !== ($this->connID = sqlsrv_connect($this->hostname, $connection)))
		{
			/* Disable warnings as errors behavior. */
			sqlsrv_configure('WarningsReturnAsErrors', 0);

			// Determine how identifiers are escaped
			$query = $this->query('SELECT CASE WHEN (@@OPTIONS | 256) = @@OPTIONS THEN 1 ELSE 0 END AS qi');
			$query = $query->getResultObject();

			$this->_quoted_identifier = empty($query) ? false : (bool) $query[0]->qi;
			$this->escapeChar         = ($this->_quoted_identifier) ? '"' : ['[', ']'];
		}

		return $this->connID;
	}

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

	/**
	 * Close the database connection.
	 * 关闭数据库连接
	 *
	 * @return void
	 */
	protected function _close()
	{
		sqlsrv_close($this->connID);
	}

	/**
	 * Platform-dependant string escape
	 * 与平台相关的字符串转义
	 *
	 * @param  string $str
	 * @return string
	 */
	protected function _escapeString(string $str): string
	{
		return str_replace("'", "''", remove_invisible_characters($str, false));
	}

	/**
	 * Insert ID
	 * 插入ID
	 *
	 * @return integer
	 */
	public function insertID(): int
	{
		return $this->query('SELECT SCOPE_IDENTITY() AS insert_id')->getRow()->insert_id ?? 0;
	}

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
		$sql = 'SELECT [TABLE_NAME] AS "name"'
				. ' FROM [INFORMATION_SCHEMA].[TABLES] '
				. ' WHERE '
				. " [TABLE_SCHEMA] = '" . $this->schema . "'    ";

		if ($prefixLimit === true && $this->DBPrefix !== '')
		{
			$sql .= " AND [TABLE_NAME] LIKE '" . $this->escapeLikeString($this->DBPrefix) . "%' "
					. sprintf($this->likeEscapeStr, $this->likeEscapeChar);
		}

		return $sql;
	}

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
		return 'SELECT [COLUMN_NAME] '
				. ' FROM [INFORMATION_SCHEMA].[COLUMNS]'
				. ' WHERE  [TABLE_NAME] = ' . $this->escape($this->DBPrefix . $table)
				. ' AND [TABLE_SCHEMA] = ' . $this->escape($this->schema);
	}

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
		$sql = 'EXEC sp_helpindex ' . $this->escape($table);

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException(lang('Database.failGetIndexData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj       = new stdClass();
			$obj->name = $row->index_name;

			$_fields     = explode(',', trim($row->index_keys));
			$obj->fields = array_map(function ($v) {
				return trim($v);
			}, $_fields);

			if (strpos($row->index_description, 'primary key located on') !== false)
			{
				$obj->type = 'PRIMARY';
			}
			else
			{
				$obj->type = (strpos($row->index_description, 'nonclustered, unique') !== false) ? 'UNIQUE' : 'INDEX';
			}

			$retVal[$obj->name] = $obj;
		}

		return $retVal;
	}

	/**
	 * Returns an array of objects with Foreign key data
	 * referenced_object_id  parent_object_id
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 */
	public function _foreignKeyData(string $table): array
	{
		$sql = 'SELECT '
				. 'f.name as constraint_name, '
				. 'OBJECT_NAME (f.parent_object_id) as table_name, '
				. 'COL_NAME(fc.parent_object_id,fc.parent_column_id) column_name, '
				. 'OBJECT_NAME(f.referenced_object_id) foreign_table_name, '
				. 'COL_NAME(fc.referenced_object_id,fc.referenced_column_id) foreign_column_name '
				. 'FROM  '
				. 'sys.foreign_keys AS f '
				. 'INNER JOIN  '
				. 'sys.foreign_key_columns AS fc  '
				. 'ON f.OBJECT_ID = fc.constraint_object_id '
				. 'INNER JOIN  '
				. 'sys.tables t  '
				. 'ON t.OBJECT_ID = fc.referenced_object_id '
				. 'WHERE  '
				. 'OBJECT_NAME (f.parent_object_id) = ' . $this->escape($table);

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException(lang('Database.failGetForeignKeyData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj                      = new stdClass();
			$obj->constraint_name     = $row->constraint_name;
			$obj->table_name          = $row->table_name;
			$obj->column_name         = $row->column_name;
			$obj->foreign_table_name  = $row->foreign_table_name;
			$obj->foreign_column_name = $row->foreign_column_name;
			$retVal[]                 = $obj;
		}

		return $retVal;
	}

	/**
	 * Disables foreign key checks temporarily.
	 * 暂时禁用外键检查
	 *
	 * @return string
	 */
	protected function _disableForeignKeyChecks()
	{
		return 'EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT ALL"';
	}

	/**
	 * Enables foreign key checks temporarily.
	 * 临时启用外键检查
	 *
	 * @return string
	 */
	protected function _enableForeignKeyChecks()
	{
		return 'EXEC sp_MSforeachtable "ALTER TABLE ? WITH CHECK CHECK CONSTRAINT ALL"';
	}

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
		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME= ' . $this->escape(($table));

		if (($query = $this->query($sql)) === false)
		{
			throw new DatabaseException(lang('Database.failGetFieldData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retVal[$i]             = new stdClass();
			$retVal[$i]->name       = $query[$i]->COLUMN_NAME;
			$retVal[$i]->type       = $query[$i]->DATA_TYPE;
			$retVal[$i]->default    = $query[$i]->COLUMN_DEFAULT;
			$retVal[$i]->max_length = $query[$i]->CHARACTER_MAXIMUM_LENGTH > 0 ? $query[$i]->CHARACTER_MAXIMUM_LENGTH : $query[$i]->NUMERIC_PRECISION;
		}

		return $retVal;
	}

	/**
	 * Begin Transaction
	 * 开始事务
	 *
	 * @return boolean
	 */
	protected function _transBegin(): bool
	{
		return (bool) sqlsrv_begin_transaction($this->connID);
	}

	/**
	 * Commit Transaction
	 * 提交事务
	 *
	 * @return boolean
	 */
	protected function _transCommit(): bool
	{
		return (bool) sqlsrv_commit($this->connID);
	}

	/**
	 * Rollback Transaction
	 * 回滚事务
	 *
	 * @return boolean
	 */
	protected function _transRollback(): bool
	{
		return (bool) sqlsrv_rollback($this->connID);
	}

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
		$error = [
			'code'    => '00000',
			'message' => '',
		];

		$sqlsrvErrors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

		if (! is_array($sqlsrvErrors))
		{
			return $error;
		}

		$sqlsrvError = array_shift($sqlsrvErrors);
		if (isset($sqlsrvError['SQLSTATE']))
		{
			$error['code'] = isset($sqlsrvError['code']) ? $sqlsrvError['SQLSTATE'] . '/' . $sqlsrvError['code'] : $sqlsrvError['SQLSTATE'];
		}
		elseif (isset($sqlsrvError['code']))
		{
			$error['code'] = $sqlsrvError['code'];
		}

		if (isset($sqlsrvError['message']))
		{
			$error['message'] = $sqlsrvError['message'];
		}

		return $error;
	}

	/**
	 * Returns the total number of rows affected by this query.
	 * 返回受此查询影响的行的总数
	 *
	 * @return integer
	 */
	public function affectedRows(): int
	{
		return sqlsrv_rows_affected($this->resultID);
	}

	/**
	 * Select a specific database table to use.
	 * 选择要使用的特定数据库表
	 *
	 * @param string|null $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName = null)
	{
		if (empty($databaseName))
		{
			$databaseName = $this->database;
		}

		if (empty($this->connID))
		{
			$this->initialize();
		}

		if ($this->execute('USE ' . $this->_escapeString($databaseName)))
		{
			$this->database  = $databaseName;
			$this->dataCache = [];
			return true;
		}

		return false;
	}

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
		$stmt = ($this->scrollable === false || $this->isWriteType($sql)) ?
			sqlsrv_query($this->connID, $sql) :
			sqlsrv_query($this->connID, $sql, [], ['Scrollable' => $this->scrollable]);

		if ($stmt === false)
		{
			$error = $this->error();

			log_message('error', $error['message']);
			if ($this->DBDebug)
			{
				throw new Exception($error['message']);
			}
		}

		return $stmt;
	}

	/**
	 * Determines if a query is a "write" type.
	 * 确定查询是否是“写”类型
	 *
	 * @param  string $sql An SQL query string
	 * @return boolean
	 */
	public function isWriteType($sql)
	{
		return (bool) preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX|MERGE)\s/i', $sql);
	}

	/**
	 * Returns the last error encountered by this connection.
	 * 返回此连接遇到的最后一个错误
	 *
	 * @return mixed
	 */
	public function getError()
	{
		$error = [
			'code'    => '00000',
			'message' => '',
		];

		$sqlsrvErrors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

		if (! is_array($sqlsrvErrors))
		{
			return $error;
		}

		$sqlsrvError = array_shift($sqlsrvErrors);
		if (isset($sqlsrvError['SQLSTATE']))
		{
			$error['code'] = isset($sqlsrvError['code']) ? $sqlsrvError['SQLSTATE'] . '/' . $sqlsrvError['code'] : $sqlsrvError['SQLSTATE'];
		}
		elseif (isset($sqlsrvError['code']))
		{
			$error['code'] = $sqlsrvError['code'];
		}

		if (isset($sqlsrvError['message']))
		{
			$error['message'] = $sqlsrvError['message'];
		}

		return $error;
	}

	/**
	 * The name of the platform in use (MySQLi, mssql, etc)
	 * 使用的平台名称（mysql、mssql等）
	 *
	 * @return string
	 */
	public function getPlatform(): string
	{
		return $this->DBDriver;
	}

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

		if (! $this->connID || empty($info = sqlsrv_server_info($this->connID)))
		{
			$this->initialize();
		}

		return isset($info['SQLServerVersion']) ? $this->dataCache['version'] = $info['SQLServerVersion'] : false;
	}
}
