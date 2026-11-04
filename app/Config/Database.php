<?php
/**
 * 配置，数据库
 */

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 * 数据库配置。
 */
class Database extends Config
{
	/**
	 * The directory that holds the Migrations
	 * and Seeds directories.
	 * 包含迁移和种子目录的目录
	 *
	 * @var string
	 */
	public $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

	/**
	 * Lets you choose which connection group to
	 * use if no other is specified.
	 * 允许你选择要使用的连接组，如果未指定其他则使用默认组。
	 *
	 * @var string
	 */
	public $defaultGroup = 'default';

	/**
	 * The default database connection.
	 * 默认的数据库连接
	 *
	 * @var array
	 */
	public $default = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => '',
		'password' => '',
		'database' => '',
		'DBDriver' => 'MySQLi',
		'DBPrefix' => '',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	/**
	 * This database connection is used when
	 * running PHPUnit database tests.
	 * 在运行 PHPUnit 数据库测试时，会使用此数据库连接。
	 *
	 * @var array
	 */
	public $tests = [
		'DSN'      => '',
		'hostname' => '127.0.0.1',
		'username' => '',
		'password' => '',
		'database' => ':memory:',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	//--------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		// Ensure that we always set the database group to 'tests' if
		// we are currently running an automated test suite, so that
		// we don't overwrite live data on accident.
		// 在运行自动化测试套件时，请确保始终将数据库组设置为“tests”，以免意外覆盖生产数据。
		if (ENVIRONMENT === 'testing')
		{
			$this->defaultGroup = 'tests';
		}
	}

	//--------------------------------------------------------------------

}
