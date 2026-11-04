<?php
/**
 * CodeIgniter，数据库，MySQLi，准备查询
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

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;

/**
 * Prepared query for MySQLi
 * 为MySQLi准备的查询
 */
class PreparedQuery extends BasePreparedQuery
{
	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 * 准备针对数据库的查询，并保存后续执行查询所需的连接信息。
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *                        Unused in the MySQLi driver.
	 *
	 * @return mixed
	 */
	public function _prepare(string $sql, array $options = [])
	{
		// Mysqli driver doesn't like statements
		// with terminating semicolons.
		// Mysqli 驱动程序不喜欢带有分号结尾的语句
		$sql = rtrim($sql, ';');

		if (! $this->statement = $this->db->mysqli->prepare($sql))
		{
			$this->errorCode   = $this->db->mysqli->errno;
			$this->errorString = $this->db->mysqli->error;
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

		// First off -bind the parameters
		$bindTypes = '';

		// Determine the type string
		foreach ($data as $item)
		{
			if (is_integer($item))
			{
				$bindTypes .= 'i';
			}
			elseif (is_numeric($item))
			{
				$bindTypes .= 'd';
			}
			else
			{
				$bindTypes .= 's';
			}
		}

		// Bind it
		$this->statement->bind_param($bindTypes, ...$data);

		return $this->statement->execute();
	}

	/**
	 * Returns the result object for the prepared query.
	 * 返回准备好的查询的结果对象
	 *
	 * @return mixed
	 */
	public function _getResult()
	{
		return $this->statement->get_result();
	}
}
