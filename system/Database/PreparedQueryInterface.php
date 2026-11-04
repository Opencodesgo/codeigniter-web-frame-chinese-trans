<?php
/**
 * CodeIgniter，数据库，预置查询接口
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

/**
 * Prepared query interface
 * 预置查询接口。
 */
interface PreparedQueryInterface
{
	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 * 接收一组新的数据，并将其与当前已准备好的查询进行对比。成功后，将返回一个 Results 对象。
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	public function execute(...$data);

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 * 准备针对数据库的查询，并保存后续执行查询所需的连接信息。
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *
	 * @return mixed
	 */
	public function prepare(string $sql, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Explicity closes the statement.
	 * 显式关闭语句
	 */
	public function close();

	//--------------------------------------------------------------------

	/**
	 * Returns the SQL that has been prepared.
	 * 返回已准备好的SQL
	 *
	 * @return string
	 */
	public function getQueryString(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 * 返回在执行此语句时创建的错误代码
	 *
	 * @return integer
	 */
	public function getErrorCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 * 返回在执行此语句时创建的错误消息
	 *
	 * @return string
	 */
	public function getErrorMessage(): string;

	//--------------------------------------------------------------------
}
