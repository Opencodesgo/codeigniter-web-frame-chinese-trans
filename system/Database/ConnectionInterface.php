<?php
/**
 * CodeIgniter，数据库，连接接口
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
 * Interface ConnectionInterface
 * 连接接口。
 */
interface ConnectionInterface
{
	/**
	 * Initializes the database connection/settings.
	 * 初始化数据库连接/设置
	 *
	 * @return mixed
	 */
	public function initialize();

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 * 连接数据库
	 *
	 * @param  boolean $persistent
	 * @return mixed
	 */
	public function connect(bool $persistent = false);

	//--------------------------------------------------------------------

	/**
	 * Create a persistent database connection.
	 * 创建持久数据库连接
	 *
	 * @return mixed
	 */
	public function persistentConnect();

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 * 如果一段时间内未发送查询，且该时间超过服务器的空闲超时时间，则保持或建立连接。
	 *
	 * @return mixed
	 */
	public function reconnect();

	//--------------------------------------------------------------------

	/**
	 * Returns the actual connection object. If both a 'read' and 'write'
	 * connection has been specified, you can pass either term in to
	 * get that connection. If you pass either alias in and only a single
	 * connection is present, it must return the sole connection.
	 * 返回实际的连接对象。如果同时指定了“读取”和“写入”连接，您可以传入其中任意一个术语来获取该连接。
	 *
	 * @param string|null $alias
	 *
	 * @return mixed
	 */
	public function getConnection(string $alias = null);

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 * 选择要使用的特定数据库表
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the current database being used.
	 * 返回正在使用的当前数据库的名称
	 *
	 * @return string
	 */
	public function getDatabase(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the last error encountered by this connection.
	 * 返回此连接遇到的最后一个错误
	 *
	 * @return array
	 */
	public function error(): array;

	//--------------------------------------------------------------------

	/**
	 * The name of the platform in use (MySQLi, mssql, etc)
	 * 使用的平台名称（mysql、mssql等）
	 *
	 * @return string
	 */
	public function getPlatform(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 * 返回包含正在使用的数据库版本的字符串
	 *
	 * @return string
	 */
	public function getVersion(): string;

	//--------------------------------------------------------------------

	/**
	 * Orchestrates a query against the database. Queries must use
	 * Database\Statement objects to store the query and build it.
	 * This method works with the cache.
	 * 对数据库执行查询。查询必须使用 Database\Statement 对象来存储和构建查询。此方法支持缓存。
	 *
	 * Should automatically handle different connections for read/write
	 * queries if needed.
	 *
	 * @param string $sql
	 * @param mixed  ...$binds
	 *
	 * @return mixed
	 */
	public function query(string $sql, $binds = null);

	//--------------------------------------------------------------------

	/**
	 * Performs a basic query against the database. No binding or caching
	 * is performed, nor are transactions handled. Simply takes a raw
	 * query string and returns the database-specific result id.
	 * 对数据库执行基本查询。不进行绑定或缓存，也不处理事务。
	 * 仅接收原始查询字符串，并返回与数据库相关的结果ID。
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function simpleQuery(string $sql);

	//--------------------------------------------------------------------

	/**
	 * Returns an instance of the query builder for this connection.
	 * 返回此连接的查询生成器的实例
	 *
	 * @param string|array $tableName Table name.
	 *
	 * @return BaseBuilder Builder.
	 */
	public function table($tableName);

	//--------------------------------------------------------------------

	/**
	 * Returns the last query's statement object.
	 * 返回最后一个查询的语句对象
	 *
	 * @return mixed
	 */
	public function getLastQuery();

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escaping
	 * “智能”转义。
	 *
	 * Escapes data based on type.
	 * Sets boolean and null types.
	 *
	 * @param mixed $str
	 *
	 * @return mixed
	 */
	public function escape($str);

	//--------------------------------------------------------------------

	/**
	 * Allows for custom calls to the database engine that are not
	 * supported through our database layer.
	 * 允许对数据库引擎进行自定义调用，这些调用在我们的数据库层中不被支持。
	 *
	 * @param string $functionName
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	public function callFunction(string $functionName, ...$params);

	//--------------------------------------------------------------------
}
