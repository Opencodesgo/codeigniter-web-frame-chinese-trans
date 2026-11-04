<?php
/**
 * CodeIgniter，数据库，SQLSRV，预置查询
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

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;
use Exception;

/**
 * Prepared query for Postgre
 * SQLSRV 预置查询。
 */
class PreparedQuery extends BasePreparedQuery
{
	/**
	 * Parameters array used to store the dynamic variables.
	 * 用于存储动态变量的参数数组
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * The result boolean from a sqlsrv_execute.
	 * sqlsrv_execute的结果布尔值
	 *
	 * @var boolean
	 */
	protected $result;

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 * 准备针对数据库的查询，并保存后续执行查询所需的连接信息。
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options Options takes an associative array;
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function _prepare(string $sql, array $options = [])
	{
		/* Prepare parameters for the query */
		$queryString = $this->getQueryString();

		$parameters = $this->parameterize($queryString);

		/* Prepare  the query */
		$this->statement = sqlsrv_prepare($this->db->connID, $sql, $parameters);

		if (! $this->statement)
		{
			$info              = $this->db->error();
			$this->errorCode   = $info['code'];
			$this->errorString = $info['message'];
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

		foreach ($data as $key => $value)
		{
			$this->parameters[$key] = $value;
		}

		$this->result = sqlsrv_execute($this->statement);

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
	 * Handle parameters
	 * 处理参数
	 *
	 * @param string $queryString
	 *
	 * @return array
	 */
	protected function parameterize(string $queryString): array
	{
		$numberOfVariables = substr_count($queryString, '?');

		$params = [];

		for ($c = 0; $c < $numberOfVariables; $c++)
		{
			$this->parameters[$c] = null;
			$params[]             = &$this->parameters[$c];
		}

		return $params;
	}
}
