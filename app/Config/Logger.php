<?php
/**
 * 配置，日志记录器
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Error Logging Threshold	错误日志阈值
	 * --------------------------------------------------------------------------
	 *
	 * You can enable error logging by setting a threshold over zero. The
	 * threshold determines what gets logged. Any values below or equal to the
	 * threshold will be logged.
	 * 可以通过将阈值设置为大于零来启用错误日志记录。
	 * 阈值决定了哪些内容会被记录。所有低于或等于该阈值的数值都将被记录。
	 *
	 * Threshold options are:
	 *
	 * - 0 = Disables logging, Error logging TURNED OFF
	 * - 1 = Emergency Messages - System is unusable
	 * - 2 = Alert Messages - Action Must Be Taken Immediately
	 * - 3 = Critical Messages - Application component unavailable, unexpected exception.
	 * - 4 = Runtime Errors - Don't need immediate action, but should be monitored.
	 * - 5 = Warnings - Exceptional occurrences that are not errors.
	 * - 6 = Notices - Normal but significant events.
	 * - 7 = Info - Interesting events, like user logging in, etc.
	 * - 8 = Debug - Detailed debug information.
	 * - 9 = All Messages
	 *
	 * You can also pass an array with threshold levels to show individual error types
	 *
	 *     array(1, 2, 3, 8) = Emergency, Alert, Critical, and Debug messages
	 *
	 * For a live site you'll usually enable Critical or higher (3) to be logged otherwise
	 * your log files will fill up very fast.
	 *
	 * @var integer|array
	 */
	public $threshold = 4;

	/**
	 * --------------------------------------------------------------------------
	 * Date Format for Logs	日志日期格式
	 * --------------------------------------------------------------------------
	 *
	 * Each item that is logged has an associated date. You can use PHP date
	 * codes to set your own date formatting
	 * 每个记录的项目都有一个关联的日期。您可以使用 PHP 日期格式代码来设置自定义的日期格式。
	 *
	 * @var string
	 */
	public $dateFormat = 'Y-m-d H:i:s';

	/**
	 * --------------------------------------------------------------------------
	 * Log Handlers	日志处理程序
	 * --------------------------------------------------------------------------
	 *
	 * The logging system supports multiple actions to be taken when something
	 * is logged. This is done by allowing for multiple Handlers, special classes
	 * designed to write the log to their chosen destinations, whether that is
	 * a file on the getServer, a cloud-based service, or even taking actions such
	 * as emailing the dev team.
	 * 日志系统支持在日志记录时使用的多个操作。
	 * 这是通过允许多个处理程序、专门为其选择的目的地编写日志来完成的,无论是在getServer上是一个文件,一个基于云的服务,甚至是作为邮件发送开发团队的操作。
	 *
	 * Each handler is defined by the class name used for that handler, and it
	 * MUST implement the `CodeIgniter\Log\Handlers\HandlerInterface` interface.
	 *
	 * The value of each key is an array of configuration items that are sent
	 * to the constructor of each handler. The only required configuration item
	 * is the 'handles' element, which must be an array of integer log levels.
	 * This is most easily handled by using the constants defined in the
	 * `Psr\Log\LogLevel` class.
	 *
	 * Handlers are executed in the order defined in this array, starting with
	 * the handler on top and continuing down.
	 *
	 * @var array
	 */
	public $handlers = [

		/*
		 * --------------------------------------------------------------------
		 * File Handler	文件处理器
		 * --------------------------------------------------------------------
		 */
		'CodeIgniter\Log\Handlers\FileHandler' => [

			/*
			 * The log levels that this handler will handle.
			 * 此处理程序将处理的日志级别
			 */
			'handles'         => [
				'critical',
				'alert',
				'emergency',
				'debug',
				'error',
				'info',
				'notice',
				'warning',
			],

			/*
			 * The default filename extension for log files.
			 * An extension of 'php' allows for protecting the log files via basic
			 * scripting, when they are to be stored under a publicly accessible directory.
			 *
			 * Note: Leaving it blank will default to 'log'.
			 */
			'fileExtension'   => '',

			/*
			 * The file system permissions to be applied on newly created log files.
			 *
			 * IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
			 * integer notation (i.e. 0700, 0644, etc.)
			 */
			'filePermissions' => 0644,

			/*
			 * Logging Directory Path
			 *
			 * By default, logs are written to WRITEPATH . 'logs/'
			 * Specify a different destination here, if desired.
			 */
			'path'            => '',
		],

		/**
		 * The ChromeLoggerHandler requires the use of the Chrome web browser
		 * and the ChromeLogger extension. Uncomment this block to use it.
		 * ChromeLoggerHandler需要使用Chrome web浏览器和ChromeLogger扩展。不要注释这个块来使用它。
		 */
		//      'CodeIgniter\Log\Handlers\ChromeLoggerHandler' => [
		//          /*
		//           * The log levels that this handler will handle.
		//           */
		//          'handles' => ['critical', 'alert', 'emergency', 'debug',
		//                        'error', 'info', 'notice', 'warning'],
		//      ]
	];
}
