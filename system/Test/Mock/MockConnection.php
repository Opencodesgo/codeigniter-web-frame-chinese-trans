<?php
/**
 * CodeIgniter，测试，模拟，模拟连接
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Query;

class MockConnection extends BaseConnection
{
	protected $returnValues = [];

	public $database;

	public $lastQuery;

	//--------------------------------------------------------------------

	public function shouldReturn(string $method, $return)
	{
		$this->returnValues[$method] = $return;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Orchestrates a query against the database. Queries must use
	 * Database\Statement objects to store the query and build it.
	 * This method works with the cache.
	 * 针对数据库编排查询。
	 * 查询必须使用 Database\Statement 对象来存储并构建查询。此方法与缓存配合使用。
	 *
	 * Should automatically handle different connections for read/write
	 * queries if needed.
	 * 应根据需要自动处理读写查询的不同连接。
	 *
	 * @param string  $sql
	 * @param mixed   ...$binds
	 * @param boolean $setEscapeFlags
	 * @param string  $queryClass
	 *
	 * @return BaseResult|Query|false
	 *
	 * @todo BC set $queryClass default as null in 4.1
	 */
	public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = '')
	{
		$queryClass = str_replace('Connection', 'Query', static::class);

		$query = new $queryClass($this);

		$query->setQuery($sql, $binds, $setEscapeFlags);

		if (! empty($this->swapPre) && ! empty($this->DBPrefix))
		{
			$query->swapPrefix($this->DBPrefix, $this->swapPre);
		}

		$startTime = microtime(true);

		$this->lastQuery = $query;

		// Run the query
		if (false === ($this->resultID = $this->simpleQuery($query->getQuery())))
		{
			$query->setDuration($startTime, $startTime);

			// @todo deal with errors

			return false;
		}

		$query->setDuration($startTime);

		$resultClass = str_replace('Connection', 'Result', get_class($this));

		return new $resultClass($this->connID, $this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 * 连接数据库
	 *
	 * @param boolean $persistent
	 *
	 * @return mixed
	 */
	public function connect(bool $persistent = false)
	{
		$return = $this->returnValues['connect'] ?? true;

		if (is_array($return))
		{
			// By removing the top item here, we can
			// get a different value for, say, testing failover connections.
			// 通过删除这里的顶部项,我们可以得到不同的值,比如测试故障转移连接。
			$return = array_shift($this->returnValues['connect']);
		}

		return $return;
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 * 如果一段时间内未发送查询，且该时间超过服务器的空闲超时时间，则保持或建立连接。
	 *
	 * @return boolean
	 */
	public function reconnect(): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 * 选择要使用的特定数据库表
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName)
	{
		$this->database = $databaseName;

		return $this;
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
		return CodeIgniter::CI_VERSION;
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
	protected function execute(string $sql)
	{
		return $this->returnValues['execute'];
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
		return 1;
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
			'code'    => null,
			'message' => null,
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
		return $this->connID->insert_id; // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 * 以平台相关的方式生成用于列出表的SQL
	 *
	 * @param boolean $constrainByPrefix
	 *
	 * @return string
	 */
	protected function _listTables(bool $constrainByPrefix = false): string
	{
		return '';
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
		return '';
	}

	/**
	 * @param  string $table
	 * @return array
	 */
	protected function _fieldData(string $table): array
	{
		return [];
	}

	/**
	 * @param  string $table
	 * @return array
	 */
	protected function _indexData(string $table): array
	{
		return [];
	}

	/**
	 * @param  string $table
	 * @return array
	 */
	protected function _foreignKeyData(string $table): array
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Close the connection.
	 * 关闭连接
	 */
	protected function _close()
	{
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
		return true;
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
		return true;
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
		return true;
	}

	//--------------------------------------------------------------------
}
