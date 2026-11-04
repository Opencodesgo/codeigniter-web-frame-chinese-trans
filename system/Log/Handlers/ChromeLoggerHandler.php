<?php
/**
 * CodeIgniter，日志，处理程序，Chrome 日志处理程序
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Class ChromeLoggerHandler
 * Chrome日志处理程序类。
 *
 * Allows for logging items to the Chrome console for debugging.
 * Requires the ChromeLogger extension installed in your browser.
 * 可将项目记录到 Chrome 控制台以进行调试。
 * 需要在浏览器中安装 ChromeLogger 扩展程序。
 *
 * @see https://craig.is/writing/chrome-logger
 */
class ChromeLoggerHandler extends BaseHandler
{
	/**
	 * Version of this library - for ChromeLogger use.
	 * 此库的版本-用于ChromeLogger使用
	 *
	 * @var float
	 */
	const VERSION = 1.0;

	/**
	 * The number of track frames returned from the backtrace.
	 * 从回溯中返回的跟踪帧数
	 *
	 * @var integer
	 */
	protected $backtraceLevel = 0;

	/**
	 * The final data that is sent to the browser.
	 * 发送到浏览器的最终数据
	 *
	 * @var array
	 */
	protected $json = [
		'version' => self::VERSION,
		'columns' => [
			'log',
			'backtrace',
			'type',
		],
		'rows'    => [],
	];

	/**
	 * The header used to pass the data.
	 * 用于传递数据的报头
	 *
	 * @var string
	 */
	protected $header = 'X-ChromeLogger-Data';

	/**
	 * Maps the log levels to the ChromeLogger types.
	 * 将日志级别映射到ChromeLogger类型
	 *
	 * @var array
	 */
	protected $levels = [
		'emergency' => 'error',
		'alert'     => 'error',
		'critical'  => 'error',
		'error'     => 'error',
		'warning'   => 'warn',
		'notice'    => 'warn',
		'info'      => 'info',
		'debug'     => 'info',
	];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$request = Services::request(null, true);

		$this->json['request_uri'] = (string) $request->uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles logging the message.
	 * If the handler returns false, then execution of handlers
	 * will stop. Any handlers that have not run, yet, will not
	 * be run.
	 * 处理记录消息。
	 * 如果处理器返回 false，则处理器的执行将停止。任何尚未运行的处理器都不会被执行。
	 *
	 * @param string $level
	 * @param string $message
	 *
	 * @return boolean
	 */
	public function handle($level, $message): bool
	{
		// Format our message
		$message = $this->format($message);

		// Generate Backtrace info
		$backtrace = debug_backtrace(0, $this->backtraceLevel);
		$backtrace = end($backtrace);

		$backtraceMessage = 'unknown';
		if (isset($backtrace['file']) && isset($backtrace['line']))
		{
			$backtraceMessage = $backtrace['file'] . ':' . $backtrace['line'];
		}

		// Default to 'log' type.
		$type = '';

		if (array_key_exists($level, $this->levels))
		{
			$type = $this->levels[$level];
		}

		$this->json['rows'][] = [
			[$message],
			$backtraceMessage,
			$type,
		];

		$this->sendLogs();

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Converts the object to display nicely in the Chrome Logger UI.
	 * 将对象转换为在Chrome Logger UI中显示良好
	 *
	 * @param mixed $object
	 *
	 * @return array
	 */
	protected function format($object)
	{
		if (! is_object($object))
		{
			return $object;
		}

		// @todo Modify formatting of objects once we can view them in browser.
		$objectArray = (array) $object;

		$objectArray['___class_name'] = get_class($object);

		return $objectArray;
	}

	//--------------------------------------------------------------------

	/**
	 * Attaches the header and the content to the passed in request object.
	 * 将标头和内容附加到传入的请求对象
	 *
	 * @param ResponseInterface $response
	 */
	public function sendLogs(ResponseInterface &$response = null)
	{
		if (is_null($response))
		{
			$response = Services::response(null, true);
		}

		$data = base64_encode(utf8_encode(json_encode($this->json)));

		$response->setHeader($this->header, $data);
	}
}
