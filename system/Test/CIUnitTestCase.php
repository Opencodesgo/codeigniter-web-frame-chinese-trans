<?php
/**
 * CodeIgniter，测试，CI 单元测试用例
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Factories;
use CodeIgniter\Events\Events;
use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\Mock\MockCache;
use CodeIgniter\Test\Mock\MockEmail;
use CodeIgniter\Test\Mock\MockSession;
use Config\Services;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * PHPunit test case.
 * 单元测试用例。
 */
abstract class CIUnitTestCase extends TestCase
{
	use ReflectionHelper;

	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $app;

	/**
	 * Methods to run during setUp.
	 * 方法在安装期间运行
	 *
	 * @var array of methods
	 */
	protected $setUpMethods = [
		'resetFactories',
		'mockCache',
		'mockEmail',
		'mockSession',
	];

	/**
	 * Methods to run during tearDown.
	 * 在拆除期间运行的方法
	 *
	 * @var array of methods
	 */
	protected $tearDownMethods = [];

	//--------------------------------------------------------------------
	// Staging	演出
	//--------------------------------------------------------------------

	/**
	 * Load the helpers.
	 * 加载辅助程序
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		helper(['url', 'test']);
	}

	protected function setUp(): void
	{
		parent::setUp();

		if (! $this->app) // @phpstan-ignore-line
		{
			$this->app = $this->createApplication();
		}

		foreach ($this->setUpMethods as $method)
		{
			$this->$method();
		}
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		foreach ($this->tearDownMethods as $method)
		{
			$this->$method();
		}
	}

	//--------------------------------------------------------------------
	// Mocking	模拟
	//--------------------------------------------------------------------

	/**
	 * Resets shared instanced for all Factories components
	 * 重置所有工厂组件的共享实例
	 */
	protected function resetFactories()
	{
		Factories::reset();
	}

	/**
	 * Resets shared instanced for all Services
	 * 重置所有服务的共享实例
	 */
	protected function resetServices()
	{
		Services::reset();
	}

	/**
	 * Injects the mock Cache driver to prevent filesystem collisions
	 * 注入模拟缓存驱动程序以防止文件系统碰撞
	 */
	protected function mockCache()
	{
		Services::injectMock('cache', new MockCache());
	}

	/**
	 * Injects the mock email driver so no emails really send
	 * 注入模拟电子邮件驱动程序，因此没有真正发送电子邮件。
	 */
	protected function mockEmail()
	{
		Services::injectMock('email', new MockEmail(config('Email')));
	}

	/**
	 * Injects the mock session driver into Services
	 * 将模拟会话驱动程序注入到服务中
	 */
	protected function mockSession()
	{
		$_SESSION = [];

		$config  = config('App');
		$session = new MockSession(new ArrayHandler($config, '0.0.0.0'), $config);

		Services::injectMock('session', $session);
	}

	//--------------------------------------------------------------------
	// Assertions	断言
	//--------------------------------------------------------------------

	/**
	 * Custom function to hook into CodeIgniter's Logging mechanism
	 * to check if certain messages were logged during code execution.
	 * 自定义函数，用于钩入 CodeIgniter 的日志机制，检查代码执行过程中是否记录了特定消息。
	 *
	 * @param string      $level
	 * @param string|null $expectedMessage
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function assertLogged(string $level, $expectedMessage = null)
	{
		$result = TestLogger::didLog($level, $expectedMessage);

		$this->assertTrue($result);
		return $result;
	}

	/**
	 * Hooks into CodeIgniter's Events system to check if a specific
	 * event was triggered or not.
	 * 钩入 CodeIgniter 的事件系统，检查特定事件是否被触发。
	 *
	 * @param string $eventName
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function assertEventTriggered(string $eventName): bool
	{
		$found     = false;
		$eventName = strtolower($eventName);

		foreach (Events::getPerformanceLogs() as $log)
		{
			if ($log['event'] !== $eventName)
			{
				continue;
			}

			$found = true;
			break;
		}

		$this->assertTrue($found);
		return $found;
	}

	/**
	 * Hooks into xdebug's headers capture, looking for a specific header
	 * emitted
	 * 钩入 xdebug 的头部捕获，查找特定发出的头部。
	 *
	 * @param string  $header     The leading portion of the header we are looking for
	 * @param boolean $ignoreCase
	 *
	 * @throws Exception
	 */
	public function assertHeaderEmitted(string $header, bool $ignoreCase = false): void
	{
		$found = false;

		if (! function_exists('xdebug_get_headers'))
		{
			$this->markTestSkipped('XDebug not found.');
		}

		foreach (xdebug_get_headers() as $emitted)
		{
			$found = $ignoreCase ?
					(stripos($emitted, $header) === 0) :
					(strpos($emitted, $header) === 0);
			if ($found)
			{
				break;
			}
		}

		$this->assertTrue($found, "Didn't find header for {$header}");
	}

	/**
	 * Hooks into xdebug's headers capture, looking for a specific header
	 * emitted
	 * 钩入 xdebug 的头部捕获，查找特定发出的头部。
	 *
	 * @param string  $header     The leading portion of the header we don't want to find
	 * @param boolean $ignoreCase
	 *
	 * @throws Exception
	 */
	public function assertHeaderNotEmitted(string $header, bool $ignoreCase = false): void
	{
		$found = false;

		if (! function_exists('xdebug_get_headers'))
		{
			$this->markTestSkipped('XDebug not found.');
		}

		foreach (xdebug_get_headers() as $emitted)
		{
			$found = $ignoreCase ?
					(stripos($emitted, $header) === 0) :
					(strpos($emitted, $header) === 0);
			if ($found)
			{
				break;
			}
		}

		$success = ! $found;
		$this->assertTrue($success, "Found header for {$header}");
	}

	/**
	 * Custom function to test that two values are "close enough".
	 * This is intended for extended execution time testing,
	 * where the result is close but not exactly equal to the
	 * expected time, for reasons beyond our control.
	 * 自定义函数，用于测试两个值是否“足够接近”。
	 * 该函数适用于长时间执行的测试场景，当由于超出我们控制的因素导致结果接近但不完全等于预期时间时使用。
	 *
	 * @param integer $expected
	 * @param mixed   $actual
	 * @param string  $message
	 * @param integer $tolerance
	 *
	 * @throws Exception
	 */
	public function assertCloseEnough(int $expected, $actual, string $message = '', int $tolerance = 1)
	{
		$difference = abs($expected - (int) floor($actual));

		$this->assertLessThanOrEqual($tolerance, $difference, $message);
	}

	/**
	 * Custom function to test that two values are "close enough".
	 * This is intended for extended execution time testing,
	 * where the result is close but not exactly equal to the
	 * expected time, for reasons beyond our control.
	 * 自定义函数，用于测试两个值是否“足够接近”。
	 * 该函数适用于长时间执行的测试场景，当由于超出我们控制的因素导致结果接近但不完全相等时使用。
	 *
	 * @param mixed   $expected
	 * @param mixed   $actual
	 * @param string  $message
	 * @param integer $tolerance
	 *
	 * @return void|boolean
	 * @throws Exception
	 */
	public function assertCloseEnoughString($expected, $actual, string $message = '', int $tolerance = 1)
	{
		$expected = (string) $expected;
		$actual   = (string) $actual;
		if (strlen($expected) !== strlen($actual))
		{
			return false;
		}

		try
		{
			$expected   = (int) substr($expected, -2);
			$actual     = (int) substr($actual, -2);
			$difference = abs($expected - $actual);

			$this->assertLessThanOrEqual($tolerance, $difference, $message);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	//--------------------------------------------------------------------
	// Utility	工具包
	//--------------------------------------------------------------------

	/**
	 * Loads up an instance of CodeIgniter
	 * and gets the environment setup.
	 * 加载一个 CodeIgniter 实例并设置环境
	 *
	 * @return CodeIgniter
	 */
	protected function createApplication()
	{
		$path = __DIR__ . '/../bootstrap.php';
		$path = realpath($path) ?: $path;
		return require $path;
	}

	/**
	 * Return first matching emitted header.
	 * 返回第一个匹配发出的标头
	 *
	 * @param string  $header     Identifier of the header of interest
	 * @param boolean $ignoreCase
	 *
	 * @return string|null The value of the header found, null if not found
	 */
	protected function getHeaderEmitted(string $header, bool $ignoreCase = false): ?string
	{
		$found = false;

		if (! function_exists('xdebug_get_headers'))
		{
			$this->markTestSkipped('XDebug not found.');
		}

		foreach (xdebug_get_headers() as $emitted)
		{
			$found = $ignoreCase ?
					(stripos($emitted, $header) === 0) :
					(strpos($emitted, $header) === 0);
			if ($found)
			{
				return $emitted;
			}
		}

		return null;
	}
}
