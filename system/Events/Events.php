<?php
/**
 * CodeIgniter，事件，Events
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Events;

use Config\Modules;
use Config\Services;

define('EVENT_PRIORITY_LOW', 200);
define('EVENT_PRIORITY_NORMAL', 100);
define('EVENT_PRIORITY_HIGH', 10);

/**
 * Events
 * 事件。
 */
class Events
{
	/**
	 * The list of listeners.
	 * 监听者列表
	 *
	 * @var array
	 */
	protected static $listeners = [];

	/**
	 * Flag to let us know if we've read from the Config file(s)
	 * and have all of the defined events.
	 * 标志用于通知我们是否已读取配置文件，并且所有定义的事件都已包含。
	 *
	 * @var boolean
	 */
	protected static $initialized = false;

	/**
	 * If true, events will not actually be fired.
	 * Useful during testing.
	 * 如果为真，事件将不会实际触发。在测试时很有用。
	 *
	 * @var boolean
	 */
	protected static $simulate = false;

	/**
	 * Stores information about the events
	 * for display in the debug toolbar.
	 * 存储有关事件的信息，以便在调试工具栏中显示。
	 *
	 * @var array<array<string, float|string>>
	 */
	protected static $performanceLog = [];

	/**
	 * A list of found files.
	 * 找到的文件列表
	 *
	 * @var string[]
	 */
	protected static $files = [];

	/**
	 * Ensures that we have a events file ready.
	 * 确保我们准备了一个事件文件
	 *
	 * @return void
	 */
	public static function initialize()
	{
		// Don't overwrite anything....
		if (static::$initialized)
		{
			return;
		}

		/**
		 * @var Modules
		 */
		$config = config('Modules');
		$events = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Events.php';
		$files  = [];

		if ($config->shouldDiscover('events'))
		{
			$files = Services::locator()->search('Config/Events.php');
		}

		$files = array_filter(array_map(static function (string $file) {
			if (is_file($file))
			{
				return realpath($file) ?: $file;
			}

			return false; // @codeCoverageIgnore
		}, $files));

		static::$files = array_unique(array_merge($files, [$events]));

		foreach (static::$files as $file)
		{
			include $file;
		}

		static::$initialized = true;
	}

	/**
	 * Registers an action to happen on an event. The action can be any sort
	 * of callable:
	 * 注册一个在事件上执行的操作。该操作可以是任何可调用类型：
	 *
	 *  Events::on('create', 'myFunction');               // procedural function
	 *  Events::on('create', ['myClass', 'myMethod']);    // Class::method
	 *  Events::on('create', [$myInstance, 'myMethod']);  // Method on an existing instance
	 *  Events::on('create', function() {});              // Closure
	 *
	 * @param string   $eventName
	 * @param callable $callback
	 * @param integer  $priority
	 *
	 * @return void
	 */
	public static function on($eventName, $callback, $priority = EVENT_PRIORITY_NORMAL)
	{
		if (! isset(static::$listeners[$eventName]))
		{
			static::$listeners[$eventName] = [
				true, // If there's only 1 item, it's sorted.
				[$priority],
				[$callback],
			];
		}
		else
		{
			static::$listeners[$eventName][0]   = false; // Not sorted
			static::$listeners[$eventName][1][] = $priority;
			static::$listeners[$eventName][2][] = $callback;
		}
	}

	/**
	 * Runs through all subscribed methods running them one at a time,
	 * until either:
	 *  a) All subscribers have finished or
	 *  b) a method returns false, at which point execution of subscribers stops.
	 * 依次运行所有已订阅的方法，直到满足以下任一条件：
	 *
	 * @param string $eventName
	 * @param mixed  $arguments
	 *
	 * @return boolean
	 */
	public static function trigger($eventName, ...$arguments): bool
	{
		// Read in our Config/Events file so that we have them all!
		if (! static::$initialized)
		{
			static::initialize();
		}

		$listeners = static::listeners($eventName);

		foreach ($listeners as $listener)
		{
			$start = microtime(true);

			$result = static::$simulate === false ? call_user_func($listener, ...$arguments) : true;

			if (CI_DEBUG)
			{
				static::$performanceLog[] = [
					'start' => $start,
					'end'   => microtime(true),
					'event' => strtolower($eventName),
				];
			}

			if ($result === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array of listeners for a single event. They are
	 * sorted by priority.
	 * 返回一个事件的监听器数组。它们按优先级排序。
	 *
	 * @param string $eventName
	 *
	 * @return array
	 */
	public static function listeners($eventName): array
	{
		if (! isset(static::$listeners[$eventName]))
		{
			return [];
		}

		// The list is not sorted
		if (! static::$listeners[$eventName][0])
		{
			// Sort it!
			array_multisort(static::$listeners[$eventName][1], SORT_NUMERIC, static::$listeners[$eventName][2]);

			// Mark it as sorted already!
			static::$listeners[$eventName][0] = true;
		}

		return static::$listeners[$eventName][2];
	}

	/**
	 * Removes a single listener from an event.
	 * 从事件中删除单个侦听器。
	 *
	 * If the listener couldn't be found, returns FALSE, else TRUE if
	 * it was removed.
	 * 如果找不到听众，则返回 FALSE；否则，如果已移除则返回 TRUE。
	 *
	 * @param string   $eventName
	 * @param callable $listener
	 *
	 * @return boolean
	 */
	public static function removeListener($eventName, callable $listener): bool
	{
		if (! isset(static::$listeners[$eventName]))
		{
			return false;
		}

		foreach (static::$listeners[$eventName][2] as $index => $check)
		{
			if ($check === $listener)
			{
				unset(static::$listeners[$eventName][1][$index]);
				unset(static::$listeners[$eventName][2][$index]);

				return true;
			}
		}

		return false;
	}

	/**
	 * Removes all listeners.
	 * 移除所有监听器。
	 *
	 * If the event_name is specified, only listeners for that event will be
	 * removed, otherwise all listeners for all events are removed.
	 * 如果指定了 event_name，则仅移除该事件的监听器；否则将移除所有事件的所有监听器。
	 *
	 * @param string|null $eventName
	 *
	 * @return void
	 */
	public static function removeAllListeners($eventName = null)
	{
		if (! is_null($eventName))
		{
			unset(static::$listeners[$eventName]);
		}
		else
		{
			static::$listeners = [];
		}
	}

	/**
	 * Sets the path to the file that routes are read from.
	 * 设置从中读取路由的文件的路径
	 *
	 * @param array $files
	 *
	 * @return void
	 */
	public static function setFiles(array $files)
	{
		static::$files = $files;
	}

	/**
	 * Returns the files that were found/loaded during this request.
	 * 返回在此请求期间找到/加载的文件
	 *
	 * @return string[]
	 */
	public function getFiles()
	{
		return static::$files;
	}

	/**
	 * Turns simulation on or off. When on, events will not be triggered,
	 * simply logged. Useful during testing when you don't actually want
	 * the tests to run.
	 * 开启或关闭模拟模式。开启后，事件将不会触发，而是直接记录。
	 * 在测试时若不想让测试运行，此功能非常有用。
	 *
	 * @param boolean $choice
	 *
	 * @return void
	 */
	public static function simulate(bool $choice = true)
	{
		static::$simulate = $choice;
	}

	/**
	 * Getter for the performance log records.
	 * 性能日志记录的Getter
	 *
	 * @return array<array<string, float|string>>
	 */
	public static function getPerformanceLogs()
	{
		return static::$performanceLog;
	}
}
