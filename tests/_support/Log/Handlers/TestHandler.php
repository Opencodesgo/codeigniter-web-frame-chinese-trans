<?php namespace Tests\Support\Log\Handlers;

/**
 * 测试，支持，日志，处理程序，测试处理器
 */

/**
 * Class TestHandler
 * 测试处理器类。
 *
 * A simple LogHandler that stores the logs in memory.
 * Only used for testing purposes.
 * 一个简单的日志处理器，将日志存储在内存中。仅用于测试目的。
 */

class TestHandler extends \CodeIgniter\Log\Handlers\FileHandler
{

	/**
	 * Local storage for logs.
	 * 日志的本地存储
	 *
	 * @var array
	 */
	protected static $logs = [];

	/**
	 * Where would the log be written?
	 * 日志在哪里写?
	 */
	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->handles     = $config['handles'] ?? [];
		$this->destination = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;

		self::$logs = [];
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
	 * @param $level
	 * @param $message
	 *
	 * @return boolean
	 */
	public function handle($level, $message): bool
	{
		$date = date($this->dateFormat);

		self::$logs[] = strtoupper($level) . ' - ' . $date . ' --> ' . $message;

		return true;
	}

	//--------------------------------------------------------------------

	public static function getLogs()
	{
		return self::$logs;
	}

	//--------------------------------------------------------------------
}
