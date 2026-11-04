<?php
/**
 * CodeIgniter，异常，调试可跟踪特性
 */

namespace CodeIgniter\Exceptions;

use Throwable;

/**
 * This trait provides framework exceptions the ability to pinpoint
 * accurately where the exception was raised rather than instantiated.
 * 该特性为框架异常提供了准确定位异常发生位置的能力，而非实例化位置。
 *
 * This is used primarily for factory-instantiated exceptions.
 * 这主要用于工厂实例化的异常。
 */
trait DebugTraceableTrait
{
	/**
	 * Tweaks the exception's constructor to assign the file/line to where
	 * it is actually raised rather than were it is instantiated.
	 * 修改异常的构造函数，使其将文件/行信息分配到实际抛出的位置，而不是实例化的位置。
	 *
	 * @param string         $message
	 * @param integer        $code
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$trace = $this->getTrace()[0];

		if (isset($trace['class']) && $trace['class'] === static::class)
		{
			['line' => $this->line, 'file' => $this->file] = $trace;
		}
	}
}
