<?php
/**
 * CodeIgniter，异常，框架异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Exceptions;

use RuntimeException;

/**
 * Class FrameworkException
 * 类框架异常。
 *
 * A collection of exceptions thrown by the framework
 * that can only be determined at run time.
 * 框架抛出的一系列异常，这些异常只能在运行时确定。
 */
class FrameworkException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	public static function forEnabledZlibOutputCompression()
	{
		return new static(lang('Core.enabledZlibOutputCompression'));
	}

	public static function forInvalidFile(string $path)
	{
		return new static(lang('Core.invalidFile', [$path]));
	}

	public static function forCopyError(string $path)
	{
		return new static(lang('Core.copyError', [$path]));
	}

	public static function forMissingExtension(string $extension)
	{
		return new static(lang('Core.missingExtension', [$extension]));
	}

	public static function forNoHandlers(string $class)
	{
		return new static(lang('Core.noHandlers', [$class]));
	}

	public static function forFabricatorCreateFailed(string $table, string $reason)
	{
		return new static(lang('Fabricator.createFailed', [$table, $reason]));
	}
}
