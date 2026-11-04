<?php
/**
 * CodeIgniter，测试，过滤器，CI 测试流过滤器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Filters;

use php_user_filter;

/**
 * Class to extract an output snapshot.
 * Used to capture output during unit testing, so that it can
 * be used in assertions.
 * 类提取输出快照。用于在单元测试期间捕获输出，以便在断言中使用。
 */
class CITestStreamFilter extends php_user_filter
{
	/**
	 * Buffer to capture stream content.
	 * 缓冲以捕获流内容
	 *
	 * @var string
	 */
	public static $buffer = '';

	/**
	 * Output filtering - catch it all.
	 * 输出过滤—捕获所有内容
	 *
	 * @param resource $in
	 * @param resource $out
	 * @param integer  $consumed
	 * @param boolean  $closing
	 *
	 * @return integer
	 */
	public function filter($in, $out, &$consumed, $closing)
	{
		while ($bucket = stream_bucket_make_writeable($in))
		{
			static::$buffer .= $bucket->data;

			$consumed += $bucket->datalen;
		}

		return PSFS_PASS_ON;
	}
}

// @codeCoverageIgnoreStart
stream_filter_register('CITestStreamFilter', 'CodeIgniter\Test\Filters\CITestStreamFilter');
// @codeCoverageIgnoreEnd
