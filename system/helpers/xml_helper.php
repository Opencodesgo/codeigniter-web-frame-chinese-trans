<?php
/**
 * System，帮助，XML 助手
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CodeIgniter XML Helpers
 * XML 助手。
 */

if (! function_exists('xml_convert'))
{
	/**
	 * Convert Reserved XML characters to Entities
	 * 将保留的XML字符转换为实体
	 *
	 * @param  string  $str
	 * @param  boolean $protectAll
	 * @return string
	 */
	function xml_convert(string $str, bool $protectAll = false): string
	{
		$temp = '__TEMP_AMPERSANDS__';

		// Replace entities to temporary markers so that
		// ampersands won't get messed up
		// 将实体替换为临时标记，以避免井号被误处理。
		$str = preg_replace('/&#(\d+);/', $temp . '\\1;', $str);

		if ($protectAll === true)
		{
			$str = preg_replace('/&(\w+);/', $temp . '\\1;', $str);
		}

		$original = [
			'&',
			'<',
			'>',
			'"',
			"'",
			'-',
		];

		$replacement = [
			'&amp;',
			'&lt;',
			'&gt;',
			'&quot;',
			'&apos;',
			'&#45;',
		];

		$str = str_replace($original, $replacement, $str);

		// Decode the temp markers back to entities
		// 将温度标记解码回实体
		$str = preg_replace('/' . $temp . '(\d+);/', '&#\\1;', $str);

		if ($protectAll === true)
		{
			return preg_replace('/' . $temp . '(\w+);/', '&\\1;', $str);
		}

		return $str;
	}
}
