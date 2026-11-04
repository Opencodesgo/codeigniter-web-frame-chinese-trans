<?php
/**
 * CodeIgniter，日志，处理程序，基本处理器
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

/**
 * Base class for logging
 * 日志记录的基类。
 */
abstract class BaseHandler implements HandlerInterface
{
	/**
	 * Handles
	 * 处理器
	 *
	 * @var array
	 */
	protected $handles;

	/**
	 * Date format for logging
	 * 日志记录的日期格式
	 *
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->handles = $config['handles'] ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether the Handler will handle logging items of this
	 * log Level.
	 * 检查处理器是否将处理此日志级别下的日志条目
	 *
	 * @param string $level
	 *
	 * @return boolean
	 */
	public function canHandle(string $level): bool
	{
		return in_array($level, $this->handles, true);
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
	abstract public function handle($level, $message): bool;

	//--------------------------------------------------------------------

	/**
	 * Stores the date format to use while logging messages.
	 * 存储记录消息时要使用的日期格式
	 *
	 * @param string $format
	 *
	 * @return HandlerInterface
	 */
	public function setDateFormat(string $format): HandlerInterface
	{
		$this->dateFormat = $format;

		return $this;
	}

	//--------------------------------------------------------------------
}
