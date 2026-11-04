<?php
/**
 * CodeIgniter，格式化，异常，格式化异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Format\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

/**
 * FormatException
 * 格式化异常。
 */
class FormatException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Thrown when the instantiated class does not exist.
	 * 当实例化的类不存在时抛出
	 *
	 * @param string $class
	 *
	 * @return FormatException
	 */
	public static function forInvalidFormatter(string $class)
	{
		return new static(lang('Format.invalidFormatter', [$class]));
	}

	/**
	 * Thrown in JSONFormatter when the json_encode produces
	 * an error code other than JSON_ERROR_NONE and JSON_ERROR_RECURSION.
	 * 当 json_encode 生成的错误码不是 JSON_ERROR_NONE 和 JSON_ERROR_RECURSION 时，会抛出 JSONFormatter。
	 *
	 * @param string $error
	 *
	 * @return FormatException
	 */
	public static function forInvalidJSON(string $error = null)
	{
		return new static(lang('Format.invalidJSON', [$error]));
	}

	/**
	 * Thrown when the supplied MIME type has no
	 * defined Formatter class.
	 * 当提供的 MIME 类型没有定义的格式化器类时抛出
	 *
	 * @param string $mime
	 *
	 * @return FormatException
	 */
	public static function forInvalidMime(string $mime)
	{
		return new static(lang('Format.invalidMime', [$mime]));
	}

	/**
	 * Thrown on XMLFormatter when the `simplexml` extension
	 * is not installed.
	 * 当未安装 `simplexml` 扩展时，由 XMLFormatter 抛出。
	 *
	 * @return FormatException
	 *
	 * @codeCoverageIgnore
	 */
	public static function forMissingExtension()
	{
		return new static(lang('Format.missingExtension'));
	}
}
