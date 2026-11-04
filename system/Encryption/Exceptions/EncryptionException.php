<?php
/**
 * CodeIgniter，加密，异常，加密异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

/**
 * Encryption exception
 * 加密异常。
 */
class EncryptionException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Thrown when no driver is present in the active encryption session.
	 * 当活动加密会话中没有驱动程序时抛出
	 *
	 * @return static
	 */
	public static function forNoDriverRequested()
	{
		return new static(lang('Encryption.noDriverRequested'));
	}

	/**
	 * Thrown when the handler requested is not available.
	 * 当请求的处理程序不可用时抛出
	 *
	 * @param string $handler
	 *
	 * @return static
	 */
	public static function forNoHandlerAvailable(string $handler)
	{
		return new static(lang('Encryption.noHandlerAvailable', [$handler]));
	}

	/**
	 * Thrown when the handler requested is unknown.
	 * 当请求的处理程序未知时抛出
	 *
	 * @param string $driver
	 *
	 * @return static
	 */
	public static function forUnKnownHandler(string $driver = null)
	{
		return new static(lang('Encryption.unKnownHandler', [$driver]));
	}

	/**
	 * Thrown when no starter key is provided for the current encryption session.
	 * 当未为当前加密会话提供启动密钥时抛出
	 *
	 * @return static
	 */
	public static function forNeedsStarterKey()
	{
		return new static(lang('Encryption.starterKeyNeeded'));
	}

	/**
	 * Thrown during data decryption when a problem or error occurred.
	 * 在数据解密期间发生问题或错误时抛出
	 *
	 * @return static
	 */
	public static function forAuthenticationFailed()
	{
		return new static(lang('Encryption.authenticationFailed'));
	}

	/**
	 * Thrown during data encryption when a problem or error occurred.
	 * 在数据加密期间发生问题或错误时抛出
	 *
	 * @return static
	 */
	public static function forEncryptionFailed()
	{
		return new static(lang('Encryption.encryptionFailed'));
	}
}
