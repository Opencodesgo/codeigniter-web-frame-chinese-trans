<?php
/**
 * CodeIgniter，异常，配置异常
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

/**
 * Exception for automatic logging.
 * 自动日志异常。
 */
class ConfigException extends CriticalError
{
	use DebugTraceableTrait;

	/**
	 * Error code
	 * 错误代码
	 *
	 * @var integer
	 */
	protected $code = 3;

	public static function forDisabledMigrations()
	{
		return new static(lang('Migrations.disabled'));
	}
}
