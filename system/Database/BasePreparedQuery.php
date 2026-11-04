<?php
/**
 * CodeIgniter，数据库，基本预置查询
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use BadMethodCallException;
use CodeIgniter\Events\Events;

/**
 * Base prepared query
 * 基本预置查询。
 */
abstract class BasePreparedQuery implements PreparedQueryInterface
{
	/**
	 * The prepared statement itself.
	 * 准备好的语句本身
	 *
	 * @var object|resource
	 */
	protected $statement;

	/**
	 * The error code, if any.
	 * 错误码（如果有的话）
	 *
	 * @var integer
	 */
	protected $errorCode;

	/**
	 * The error message, if any.
	 * 错误消息（如果有的话）
	 *
	 * @var string
	 */
	protected $errorString;

	/**
	 * Holds the prepared query object
	 * that is cloned during execute.
	 * 持有在执行期间被克隆的已准备查询对象
	 *
	 * @var Query
	 */
	protected $query;

	/**
	 * A reference to the db connection to use.
	 * 要使用的数据库连接的引用
	 *
	 * @var BaseConnection
	 */
	protected $db;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param BaseConnection $db
	 */
	public function __construct(BaseConnection $db)
	{
		$this->db = &$db;
	}

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 * 准备针对数据库的查询，并保存后续执行查询所需的连接信息。
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options    Passed to the connection's prepare statement.
	 * @param string $queryClass
	 *
	 * @return mixed
	 */
	public function prepare(string $sql, array $options = [], string $queryClass = 'CodeIgniter\\Database\\Query')
	{
		// We only supports positional placeholders (?)
		// in order to work with the execute method below, so we
		// need to replace our named placeholders (:name)
		$sql = preg_replace('/:[^\s,)]+/', '?', $sql);

		/**
		 * @var \CodeIgniter\Database\Query $query
		 */
		$query = new $queryClass($this->db);

		$query->setQuery($sql);

		if (! empty($this->db->swapPre) && ! empty($this->db->DBPrefix))
		{
			$query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
		}

		$this->query = $query;

		return $this->_prepare($query->getOriginalQuery(), $options);
	}

	//--------------------------------------------------------------------

	/**
	 * The database-dependent portion of the prepare statement.
	 * prepare语句中依赖于数据库的部分
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *
	 * @return mixed
	 */
	abstract public function _prepare(string $sql, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 * 接收一组新的数据，并将其与当前已准备好的查询进行对比。成功后，将返回一个 Results 对象。
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	public function execute(...$data)
	{
		// Execute the Query.
		// 执行查询
		$startTime = microtime(true);

		$result = $this->_execute($data);

		// Update our query object
		// 更新查询对象
		$query = clone $this->query;
		$query->setBinds($data);

		$query->setDuration($startTime);

		// Let others do something with this query
		// 让其他人对这个查询做点什么吧
		Events::trigger('DBQuery', $query);

		// Return a result object
		$resultClass = str_replace('PreparedQuery', 'Result', get_class($this));

		$resultID = $this->_getResult();

		return new $resultClass($this->db->connID, $resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * The database dependant version of the execute method.
	 * execute方法的数据库相关版本
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	abstract public function _execute(array $data): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the result object for the prepared query.
	 * 返回准备好的查询的结果对象
	 *
	 * @return mixed
	 */
	abstract public function _getResult();

	//--------------------------------------------------------------------

	/**
	 * Explicitly closes the statement.
	 * 显式地关闭语句
	 *
	 * @return void
	 */
	public function close()
	{
		if (! is_object($this->statement))
		{
			return;
		}

		$this->statement->close();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the SQL that has been prepared.
	 * 返回已准备好的SQL
	 *
	 * @return string
	 */
	public function getQueryString(): string
	{
		if (! $this->query instanceof QueryInterface)
		{
			throw new BadMethodCallException('Cannot call getQueryString on a prepared query until after the query has been prepared.');
		}

		return $this->query->getQuery();
	}

	//--------------------------------------------------------------------

	/**
	 * A helper to determine if any error exists.
	 * 确定是否存在任何错误的帮助程序
	 *
	 * @return boolean
	 */
	public function hasError(): bool
	{
		return ! empty($this->errorString);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 * 返回在执行此语句时创建的错误代码
	 *
	 * @return integer
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 * 返回在执行此语句时创建的错误消息
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		return $this->errorString;
	}

	//--------------------------------------------------------------------
}
