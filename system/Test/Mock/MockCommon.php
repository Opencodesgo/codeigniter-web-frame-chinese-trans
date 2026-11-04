<?php
/**
 * CodeIgniter，测试，模拟，模拟公共
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (! function_exists('is_cli'))
{
	/**
	 * Is CLI?
	 * 是客户端？
	 *
	 * Test to see if a request was made from the command line.
	 * You can set the return value for testing.
	 * 测试是否从命令行发出请求。你可以设置返回值用于测试。
	 *
	 * @param  boolean $newReturn return value to set
	 * @return boolean
	 */
	function is_cli(bool $newReturn = null): bool
	{
		// PHPUnit always runs via CLI.
		static $returnValue = true;

		if ($newReturn !== null)
		{
			$returnValue = $newReturn;
		}

		return $returnValue;
	}
}
