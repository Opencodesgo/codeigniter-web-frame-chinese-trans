<?php
/**
 * CodeIgniter，配置，基本服务
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Autoloader\Autoloader;
use CodeIgniter\Autoloader\FileLocator;
use Config\Autoload;
use Config\Modules;

/**
 * Services Configuration file.
 * Services 配置文件。
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This is used in place of a Dependency Injection container primarily
 * due to its simplicity, which allows a better long-term maintenance
 * of the applications built on top of CodeIgniter. A bonus side-effect
 * is that IDEs are able to determine what class you are calling
 * whereas with DI Containers there usually isn't a way for them to do this.
 *
 * @see http://blog.ircmaxell.com/2015/11/simple-easy-risk-and-change.html
 * @see http://www.infoq.com/presentations/Simple-Made-Easy
 */
class BaseService
{
	/**
	 * Cache for instance of any services that
	 * have been requested as a "shared" instance.
	 * Keys should be lowercase service names.
	 * 缓存任何已请求为“共享”实例的服务实例。键应为小写服务名称。
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Mock objects for testing which are returned if exist.
	 * 如果存在，则返回用于测试的模拟对象。
	 *
	 * @var array
	 */
	protected static $mocks = [];

	/**
	 * Have we already discovered other Services?
	 * 我们是否已经发现了其他服务？
	 *
	 * @var boolean
	 */
	protected static $discovered = false;

	/**
	 * A cache of other service classes we've found.
	 * 我们找到的其他服务类的缓存
	 *
	 * @var array
	 */
	protected static $services = [];

	/**
	 * A cache of the names of services classes found.
	 * 找到的服务类名称的缓存
	 *
	 * @var array<string>
	 */
	private static $serviceNames = [];

	/**
	 * Returns a shared instance of any of the class' services.
	 * 返回任何类服务的共享实例。
	 *
	 * $key must be a name matching a service.
	 *
	 * @param string $key
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	protected static function getSharedInstance(string $key, ...$params)
	{
		$key = strtolower($key);

		// Returns mock if exists
		if (isset(static::$mocks[$key]))
		{
			return static::$mocks[$key];
		}

		if (! isset(static::$instances[$key]))
		{
			// Make sure $getShared is false
			array_push($params, false);

			static::$instances[$key] = static::$key(...$params);
		}

		return static::$instances[$key];
	}

	/**
	 * The Autoloader class is the central class that handles our
	 * spl_autoload_register method, and helper methods.
	 *
	 * @param boolean $getShared
	 *
	 * @return Autoloader
	 */
	public static function autoloader(bool $getShared = true)
	{
		if ($getShared)
		{
			if (empty(static::$instances['autoloader']))
			{
				static::$instances['autoloader'] = new Autoloader();
			}

			return static::$instances['autoloader'];
		}

		return new Autoloader();
	}

	/**
	 * The file locator provides utility methods for looking for non-classes
	 * within namespaced folders, as well as convenience methods for
	 * loading 'helpers', and 'libraries'.
	 *
	 * @param boolean $getShared
	 *
	 * @return FileLocator
	 */
	public static function locator(bool $getShared = true)
	{
		if ($getShared)
		{
			if (empty(static::$instances['locator']))
			{
				static::$instances['locator'] = new FileLocator(static::autoloader());
			}

			return static::$mocks['locator'] ?? static::$instances['locator'];
		}

		return new FileLocator(static::autoloader());
	}

	/**
	 * Provides the ability to perform case-insensitive calling of service
	 * names.
	 * 提供对服务名称进行不区分大小写的调用功能
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		$service = static::serviceExists($name);

		if ($service === null)
		{
			return null;
		}

		return $service::$name(...$arguments);
	}

	/**
	 * Check if the requested service is defined and return the declaring
	 * class. Return null if not found.
	 * 检查所请求的服务是否已定义，并返回声明该服务的类。如果未找到则返回 null。
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	public static function serviceExists(string $name): ?string
	{
		static::buildServicesCache();
		$services = array_merge(self::$serviceNames, [Services::class]);
		$name     = strtolower($name);

		foreach ($services as $service)
		{
			if (method_exists($service, $name))
			{
				return $service;
			}
		}

		return null;
	}

	/**
	 * Reset shared instances and mocks for testing.
	 * 重置共享实例和模拟以进行测试
	 *
	 * @param boolean $initAutoloader Initializes autoloader instance
	 */
	public static function reset(bool $initAutoloader = false)
	{
		static::$mocks     = [];
		static::$instances = [];

		if ($initAutoloader)
		{
			static::autoloader()->initialize(new Autoload(), new Modules());
		}
	}

	/**
	 * Inject mock object for testing.
	 * 注入模拟对象进行测试
	 *
	 * @param string $name
	 * @param mixed  $mock
	 */
	public static function injectMock(string $name, $mock)
	{
		static::$mocks[strtolower($name)] = $mock;
	}

	/**
	 * Will scan all psr4 namespaces registered with system to look
	 * for new Config\Services files. Caches a copy of each one, then
	 * looks for the service method in each, returning an instance of
	 * the service, if available.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 *
	 * @deprecated
	 *
	 * @codeCoverageIgnore
	 */
	protected static function discoverServices(string $name, array $arguments)
	{
		if (! static::$discovered)
		{
			$config = config('Modules');

			if ($config->shouldDiscover('services'))
			{
				$locator = static::locator();
				$files   = $locator->search('Config/Services');

				if (empty($files))
				{
					// no files at all found - this would be really, really bad
					return null;
				}

				// Get instances of all service classes and cache them locally.
				foreach ($files as $file)
				{
					$classname = $locator->getClassname($file);

					if (! in_array($classname, ['CodeIgniter\\Config\\Services'], true))
					{
						static::$services[] = new $classname();
					}
				}
			}

			static::$discovered = true;
		}

		if (! static::$services)
		{
			// we found stuff, but no services - this would be really bad
			return null;
		}

		// Try to find the desired service method
		foreach (static::$services as $class)
		{
			if (method_exists($class, $name))
			{
				return $class::$name(...$arguments);
			}
		}

		return null;
	}

	protected static function buildServicesCache(): void
	{
		if (! static::$discovered)
		{
			$config = config('Modules');

			if ($config->shouldDiscover('services'))
			{
				$locator = static::locator();
				$files   = $locator->search('Config/Services');

				// Get instances of all service classes and cache them locally.
				foreach ($files as $file)
				{
					$classname = $locator->getClassname($file);

					if ($classname !== 'CodeIgniter\\Config\\Services')
					{
						self::$serviceNames[] = $classname;
						static::$services[]   = new $classname();
					}
				}
			}

			static::$discovered = true;
		}
	}
}
