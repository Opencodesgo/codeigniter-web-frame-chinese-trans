<?php
/**
 * CodeIgniter，日志，Logger
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Log;

use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Log\Handlers\HandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * The CodeIgntier Logger
 * CodeIgntier记录器。
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 */
class Logger implements LoggerInterface
{
	/**
	 * Used by the logThreshold Config setting to define
	 * which errors to show.
	 * 由 logThreshold 配置设置用于定义要显示哪些错误
	 *
	 * @var array<string, integer>
	 */
	protected $logLevels = [
		'emergency' => 1,
		'alert'     => 2,
		'critical'  => 3,
		'error'     => 4,
		'warning'   => 5,
		'notice'    => 6,
		'info'      => 7,
		'debug'     => 8,
	];

	/**
	 * Array of levels to be logged.
	 * The rest will be ignored.
	 * Set in Config/logger.php
	 * 要记录的级别数组。其余将被忽略。
	 *
	 * @var array
	 */
	protected $loggableLevels = [];

	/**
	 * File permissions
	 * 文件权限
	 *
	 * @var integer
	 */
	protected $filePermissions = 0644;

	/**
	 * Format of the timestamp for log files.
	 * 日志文件的时间戳格式
	 *
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Filename Extension
	 * 文件名扩展
	 *
	 * @var string
	 */
	protected $fileExt;

	/**
	 * Caches instances of the handlers.
	 * 缓存处理程序实例
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * Holds the configuration for each handler.
	 * The key is the handler's class name. The
	 * value is an associative array of configuration
	 * items.
	 * 存储每个处理器的配置。键是处理器的类名，值是一个包含配置项的关联数组。
	 *
	 * @var array
	 */
	protected $handlerConfig = [];

	/**
	 * Caches logging calls for debugbar.
	 * 缓存debugbar的日志调用
	 *
	 * @var array
	 */
	public $logCache;

	/**
	 * Should we cache our logged items?
	 * 我们应该缓存记录的条目吗？
	 *
	 * @var boolean
	 */
	protected $cacheLogs = false;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param  \Config\Logger $config
	 * @param  boolean        $debug
	 * @throws RuntimeException
	 */
	public function __construct($config, bool $debug = CI_DEBUG)
	{
		$this->loggableLevels = is_array($config->threshold) ? $config->threshold : range(1, (int) $config->threshold);

		// Now convert loggable levels to strings.
		// We only use numbers to make the threshold setting convenient for users.
		if ($this->loggableLevels)
		{
			$temp = [];
			foreach ($this->loggableLevels as $level)
			{
				$temp[] = array_search((int) $level, $this->logLevels, true);
			}

			$this->loggableLevels = $temp;
			unset($temp);
		}

		$this->dateFormat = $config->dateFormat ?? $this->dateFormat;

		if (! is_array($config->handlers) || empty($config->handlers))
		{
			throw LogException::forNoHandlers('LoggerConfig');
		}

		// Save the handler configuration for later.
		// Instances will be created on demand.
		$this->handlerConfig = $config->handlers;

		$this->cacheLogs = $debug;
		if ($this->cacheLogs)
		{
			$this->logCache = [];
		}
	}

	//--------------------------------------------------------------------

	/**
	 * System is unusable.
	 * 系统不可用
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function emergency($message, array $context = []): bool
	{
		return $this->log('emergency', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Action must be taken immediately.
	 * 必须立即采取行动。
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function alert($message, array $context = []): bool
	{
		return $this->log('alert', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Critical conditions.
	 * 临界状态。
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function critical($message, array $context = []): bool
	{
		return $this->log('critical', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 * 无需立即处理，但通常应记录并监控的运行时错误。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function error($message, array $context = []): bool
	{
		return $this->log('error', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Exceptional occurrences that are not errors.
	 * 不属于错误的异常情况。
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function warning($message, array $context = []): bool
	{
		return $this->log('warning', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Normal but significant events.
	 * 正常但重要的事件
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function notice($message, array $context = []): bool
	{
		return $this->log('notice', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Interesting events.
	 * 有趣的事件。
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function info($message, array $context = []): bool
	{
		return $this->log('info', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Detailed debug information.
	 * 详细的调试信息
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function debug($message, array $context = []): bool
	{
		return $this->log('debug', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Logs with an arbitrary level.
	 * 具有任意级别的日志
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function log($level, $message, array $context = []): bool
	{
		if (is_numeric($level))
		{
			$level = array_search((int) $level, $this->logLevels, true);
		}

		// Is the level a valid level?
		if (! array_key_exists($level, $this->logLevels))
		{
			throw LogException::forInvalidLogLevel($level);
		}

		// Does the app want to log this right now?
		if (! in_array($level, $this->loggableLevels, true))
		{
			return false;
		}

		// Parse our placeholders
		$message = $this->interpolate($message, $context);

		if (! is_string($message))
		{
			$message = print_r($message, true);
		}

		if ($this->cacheLogs)
		{
			$this->logCache[] = [
				'level' => $level,
				'msg'   => $message,
			];
		}

		foreach ($this->handlerConfig as $className => $config)
		{
			if (! array_key_exists($className, $this->handlers))
			{
				$this->handlers[$className] = new $className($config);
			}

			/**
			 * @var HandlerInterface
			 */
			$handler = $this->handlers[$className];

			if (! $handler->canHandle($level))
			{
				continue;
			}

			// If the handler returns false, then we
			// don't execute any other handlers.
			if (! $handler->setDateFormat($this->dateFormat)->handle($level, $message))
			{
				break;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Replaces any placeholders in the message with variables
	 * from the context, as well as a few special items like:
	 * 将消息中的任何占位符替换为上下文中的变量，以及一些特殊项目，例如：
	 *
	 * {session_vars}
	 * {post_vars}
	 * {get_vars}
	 * {env}
	 * {env:foo}
	 * {file}
	 * {line}
	 *
	 * @param mixed $message
	 * @param array $context
	 *
	 * @return mixed
	 */
	protected function interpolate($message, array $context = [])
	{
		if (! is_string($message))
		{
			return $message;
		}

		// build a replacement array with braces around the context keys
		// 在上下文键周围用大括号构建替换数组
		$replace = [];

		foreach ($context as $key => $val)
		{
			// Verify that the 'exception' key is actually an exception
			// or error, both of which implement the 'Throwable' interface.
			if ($key === 'exception' && $val instanceof Throwable)
			{
				$val = $val->getMessage() . ' ' . $this->cleanFileNames($val->getFile()) . ':' . $val->getLine();
			}

			// todo - sanitize input before writing to file?
			$replace['{' . $key . '}'] = $val;
		}

		// Add special placeholders
		$replace['{post_vars}'] = '$_POST: ' . print_r($_POST, true);
		$replace['{get_vars}']  = '$_GET: ' . print_r($_GET, true);
		$replace['{env}']       = ENVIRONMENT;

		// Allow us to log the file/line that we are logging from
		if (strpos($message, '{file}') !== false)
		{
			list($file, $line) = $this->determineFile();

			$replace['{file}'] = $file;
			$replace['{line}'] = $line;
		}

		// Match up environment variables in {env:foo} tags.
		if (strpos($message, 'env:') !== false)
		{
			preg_match('/env:[^}]+/', $message, $matches);

			if ($matches)
			{
				foreach ($matches as $str)
				{
					$key                 = str_replace('env:', '', $str);
					$replace["{{$str}}"] = $_ENV[$key] ?? 'n/a';
				}
			}
		}

		if (isset($_SESSION))
		{
			$replace['{session_vars}'] = '$_SESSION: ' . print_r($_SESSION, true);
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

	/**
	 * Determines the file and line that the logging call
	 * was made from by analyzing the backtrace.
	 * Find the earliest stack frame that is part of our logging system.
	 * 通过分析回溯信息，确定日志调用所来自的文件和行号。找出属于我们日志系统最早的栈帧。
	 *
	 * @return array
	 */
	public function determineFile(): array
	{
		$logFunctions = [
			'log_message',
			'log',
			'error',
			'debug',
			'info',
			'warning',
			'critical',
			'emergency',
			'alert',
			'notice',
		];

		// Generate Backtrace info
		$trace = \debug_backtrace(0);

		// So we search from the bottom (earliest) of the stack frames
		$stackFrames = \array_reverse($trace);

		// Find the first reference to a Logger class method
		foreach ($stackFrames as $frame)
		{
			if (\in_array($frame['function'], $logFunctions, true))
			{
				$file = isset($frame['file']) ? $this->cleanFileNames($frame['file']) : 'unknown';
				$line = $frame['line'] ?? 'unknown';
				return [
					$file,
					$line,
				];
			}
		}

		return [
			'unknown',
			'unknown',
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Cleans the paths of filenames by replacing APPPATH, SYSTEMPATH, FCPATH
	 * with the actual var. i.e.
	 * 通过将 APPPATH、SYSTEMPATH、FCPATH 替换为实际变量来清理文件名路径。
	 *
	 *  /var/www/site/app/Controllers/Home.php
	 *      becomes:
	 *  APPPATH/Controllers/Home.php
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	protected function cleanFileNames(string $file): string
	{
		$file = str_replace(APPPATH, 'APPPATH/', $file);
		$file = str_replace(SYSTEMPATH, 'SYSTEMPATH/', $file);

		return str_replace(FCPATH, 'FCPATH/', $file);
	}

	//--------------------------------------------------------------------
}
