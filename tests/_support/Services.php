<?php namespace CodeIgniter;

/**
 * CodeIgniter，服务
 */

use CIUnitTestCase;
use Config\Services as ConfigServices;

/**
 * Services class for testing.
 * 用于测试的服务类。
 */
class Services
{
	/**
	 * Mock objects for testing which are returned if exist.
	 * 如果存在，则返回用于测试的模拟对象。
	 *
	 * @var array
	 */
	static protected $mocks = [];

	//--------------------------------------------------------------------

	/**
	 * Reset shared instances and mocks for testing.
	 * 重置共享实例和模拟以进行测试
	 */
	public static function reset()
	{
		static::$mocks = [];

		CIUnitTestCase::setPrivateProperty(ConfigServices::class, 'instances', []);
	}

	//--------------------------------------------------------------------

	/**
	 * Inject mock object for testing.
	 * 注入模拟对象进行测试
	 *
	 * @param string $name
	 * @param $mock
	 */
	public static function injectMock(string $name, $mock)
	{
		$name                 = strtolower($name);
		static::$mocks[$name] = $mock;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a service
	 * 返回一个服务
	 *
	 * @param string $name
	 * @param array  $arguments
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		$name = strtolower($name);

		// Returns mock if exists
		// 如果存在则返回模拟
		if (isset(static::$mocks[$name]))
		{
			return static::$mocks[$name];
		}

		if (method_exists(ConfigServices::class, $name))
		{
			return ConfigServices::$name(...$arguments);
		}
	}

	//--------------------------------------------------------------------

}
