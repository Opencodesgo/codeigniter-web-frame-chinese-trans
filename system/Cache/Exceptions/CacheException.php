<?php
/**
 * CodeIgniter，缓存，异常，缓存异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use RuntimeException;

/**
 * CacheException
 * 缓存异常
 */
class CacheException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Thrown when handler has no permission to write cache.
	 * 当处理程序没有写缓存的权限时抛出
	 *
	 * @param string $path
	 *
	 * @return CacheException
	 */
	public static function forUnableToWrite(string $path)
	{
		return new static(lang('Cache.unableToWrite', [$path]));
	}

	/**
	 * Thrown when an unrecognized handler is used.
	 * 当使用无法识别的处理程序时抛出
	 *
	 * @return CacheException
	 */
	public static function forInvalidHandlers()
	{
		return new static(lang('Cache.invalidHandlers'));
	}

	/**
	 * Thrown when no backup handler is setup in config.
	 * 当配置中没有设置备份处理程序时抛出
	 *
	 * @return CacheException
	 */
	public static function forNoBackup()
	{
		return new static(lang('Cache.noBackup'));
	}

	/**
	 * Thrown when specified handler was not found.
	 * 当没有找到指定的处理程序时抛出
	 *
	 * @return CacheException
	 */
	public static function forHandlerNotFound()
	{
		return new static(lang('Cache.handlerNotFound'));
	}
}
