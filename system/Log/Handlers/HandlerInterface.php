<?php
/**
 * CodeIgniter，日志，处理程序，处理器接口
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
 * Expected behavior for a Log handler
 * 日志处理程序的预期行为。
 */
interface HandlerInterface
{
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
	public function handle($level, $message): bool;

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
	public function canHandle(string $level): bool;

	//--------------------------------------------------------------------

	/**
	 * Sets the preferred date format to use when logging.
	 * 设置日志记录时使用的首选日期格式
	 *
	 * @param string $format
	 *
	 * @return HandlerInterface
	 */
	public function setDateFormat(string $format);

	//--------------------------------------------------------------------
}
