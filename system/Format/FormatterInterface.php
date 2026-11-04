<?php
/**
 * CodeIgniter，格式化，格式化接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Format;

/**
 * Formatter interface
 * 格式化接口。
 */
interface FormatterInterface
{
	/**
	 * Takes the given data and formats it.
	 * 获取给定的数据并对其进行格式化
	 *
	 * @param string|array $data
	 *
	 * @return mixed
	 */
	public function format($data);
}
