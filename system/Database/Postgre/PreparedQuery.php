<?php
/**
 * CodeIgniter，数据库，Postgre，预处理查询
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Postgre;

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;
use Exception;

/**
 * Prepared query for Postgre
 * Postgre 预处理查询。
 */
class PreparedQuery extends BasePreparedQuery
{
	/**
	 * Stores the name this query can be
	 * used under by postgres. Only used internally.
	 * 存储此查询在 Postgres 中可被使用的名称。仅用于内部使用。
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The result resource from a successful
	 * pg_exec. Or false.
	 * 成功执行 pg_exec 后的结果资源，或为 false。
	 *
	 * @var Result|boolean
	 */
	protected $result;

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 * 准备针对数据库的查询，并保存后续执行查询所需的连接信息。
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 * 注意：此版本基于SQL代码。子类应重写此方法。
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *                        Unused in the MySQLi driver.
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function _prepare(string $sql, array $options = [])
	{
		$this->name = (string) random_int(1, 10000000000000000);

		$sql = $this->parameterize($sql);

		// Update the query object since the parameters are slightly different
		// than what was put in.
		// 由于参数与之前输入的略有不同，请更新查询对象。
		$this->query->setQuery($sql);

		if (! $this->statement = pg_prepare($this->db->connID, $this->name, $sql))
		{
			$this->errorCode   = 0;
			$this->errorString = pg_last_error($this->db->connID);
		}

		return $this;
	}

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 * 接收一组新的数据，并将其与当前已准备好的查询进行对比。
	 * 成功后，将返回一个 Results 对象。
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function _execute(array $data): bool
	{
		if (! isset($this->statement))
		{
			throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
		}

		$this->result = pg_execute($this->db->connID, $this->name, $data);

		return (bool) $this->result;
	}

	/**
	 * Returns the result object for the prepared query.
	 * 返回准备好的查询的结果对象
	 *
	 * @return mixed
	 */
	public function _getResult()
	{
		return $this->result;
	}

	/**
	 * Replaces the ? placeholders with $1, $2, etc parameters for use
	 * within the prepared query.
	 * 将问号占位符替换为 $1、$2 等参数，用于预处理查询中。
	 *
	 * @param string $sql
	 *
	 * @return string
	 */
	public function parameterize(string $sql): string
	{
		// Track our current value
		// 跟踪当前值
		$count = 0;

		return preg_replace_callback('/\?/', function ($matches) use (&$count) {
			$count ++;
			return "\${$count}";
		}, $sql);
	}
}
