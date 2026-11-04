<?php
/**
 * CodeIgniter，CLI，异常，CLI 异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use RuntimeException;

/**
 * CLIException
 * CLI 异常
 */
class CLIException extends RuntimeException
{
	use DebugTraceableTrait;

	/**
	 * Thrown when `$color` specified for `$type` is not within the
	 * allowed list of colors.
	 * 当 `$type` 指定的 `$color` 不在允许的颜色列表中时抛出
	 *
	 * @param string $type
	 * @param string $color
	 *
	 * @return CLIException
	 */
	public static function forInvalidColor(string $type, string $color)
	{
		return new static(lang('CLI.invalidColor', [$type, $color]));
	}
}
