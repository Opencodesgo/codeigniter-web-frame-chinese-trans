<?php
/**
 * CodeIgniter，数据库，Database
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

use InvalidArgumentException;

/**
 * Database Connection Factory
 * 数据库连接工厂。
 *
 * Creates and returns an instance of the appropriate DatabaseConnection
 * 创建并返回相应DatabaseConnection的实例。
 */
class Database
{
	/**
	 * Maintains an array of the instances of all connections that have
	 * been created.
	 * 维护一个包含所有已创建连接实例的数组。
	 * 
	 * Helps to keep track of all open connections for performance
	 * monitoring, logging, etc.
	 * 有助于跟踪所有打开的连接，用于性能监控、日志记录等。
	 *
	 * @var array
	 */
	protected $connections = [];

	//--------------------------------------------------------------------

	/**
	 * Parses the connection binds and returns an instance of the driver
	 * ready to go.
	 * 解析连接绑定并返回一个已准备就绪的驱动程序实例
	 *
	 * @param array  $params
	 * @param string $alias
	 *
	 * @return mixed
	 * 
	 * @throws InvalidArgumentException
	 * 
	 * @internal param bool $useBuilder
	 */
	public function load(array $params = [], string $alias = '')
	{
		if ($alias === '')
		{
			throw new InvalidArgumentException('You must supply the parameter: alias.');
		}

		// Handle universal DSN connection string
		// 处理通用DSN连接字符串
		if (! empty($params['DSN']) && strpos($params['DSN'], '://') !== false)
		{
			$params = $this->parseDSN($params);
		}

		// No DB specified? Beat them senseless...
		if (empty($params['DBDriver']))
		{
			throw new InvalidArgumentException('You have not selected a database type to connect to.');
		}

		// Store the connection
		$this->connections[$alias] = $this->initDriver($params['DBDriver'], 'Connection', $params);

		return $this->connections[$alias];
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a Forge instance for the current database type.
	 * 为当前数据库类型创建一个Forge实例
	 *
	 * @param ConnectionInterface $db
	 *
	 * @return object
	 */
	public function loadForge(ConnectionInterface $db): object
	{
		// Initialize database connection if not exists.
		if (! $db->connID)
		{
			$db->initialize();
		}

		return $this->initDriver($db->DBDriver, 'Forge', $db);
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a Utils instance for the current database type.
	 * 为当前数据库类型创建Utils实例
	 *
	 * @param ConnectionInterface $db
	 *
	 * @return object
	 */
	public function loadUtils(ConnectionInterface $db): object
	{
		// Initialize database connection if not exists.
		if (! $db->connID)
		{
			$db->initialize();
		}

		return $this->initDriver($db->DBDriver, 'Utils', $db);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse universal DSN string
	 * 解析通用DSN字符串
	 *
	 * @param array $params
	 *
	 * @return array
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function parseDSN(array $params): array
	{
		$dsn = parse_url($params['DSN']);

		if (! $dsn)
		{
			throw new InvalidArgumentException('Your DSN connection string is invalid.');
		}

		$dsnParams = [
			'DSN'      => '',
			'DBDriver' => $dsn['scheme'],
			'hostname' => isset($dsn['host']) ? rawurldecode($dsn['host']) : '',
			'port'     => isset($dsn['port']) ? rawurldecode((string) $dsn['port']) : '',
			'username' => isset($dsn['user']) ? rawurldecode($dsn['user']) : '',
			'password' => isset($dsn['pass']) ? rawurldecode($dsn['pass']) : '',
			'database' => isset($dsn['path']) ? rawurldecode(substr($dsn['path'], 1)) : '',
		];

		// Do we have additional config items set?
		// 我们是否设置了额外的配置项？
		if (! empty($dsn['query']))
		{
			parse_str($dsn['query'], $extra);

			foreach ($extra as $key => $val)
			{
				if (is_string($val) && in_array(strtolower($val), ['true', 'false', 'null'], true))
				{
					$val = $val === 'null' ? null : filter_var($val, FILTER_VALIDATE_BOOLEAN);
				}

				$dsnParams[$key] = $val;
			}
		}

		return array_merge($params, $dsnParams);
	}

	//--------------------------------------------------------------------

	/**
	 * Initialize database driver.
	 * 初始化数据库驱动程序
	 *
	 * @param string       $driver   Database driver name (e.g. 'MySQLi')
	 * @param string       $class    Database class name (e.g. 'Forge')
	 * @param array|object $argument
	 *
	 * @return object
	 */
	protected function initDriver(string $driver, string $class, $argument): object
	{
		$class = $driver . '\\' . $class;

		if (strpos($driver, '\\') === false)
		{
			$class = "CodeIgniter\Database\\{$class}";
		}

		return new $class($argument);
	}
}
