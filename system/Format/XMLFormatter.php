<?php
/**
 * CodeIgniter，格式化，XML 格式化
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

use CodeIgniter\Format\Exceptions\FormatException;
use Config\Format;
use SimpleXMLElement;

/**
 * XML data formatter
 * XML 数据格式化。
 */
class XMLFormatter implements FormatterInterface
{
	/**
	 * Takes the given data and formats it.
	 * 获取给定的数据并对其进行格式化
	 *
	 * @param mixed $data
	 *
	 * @return string|boolean (XML string | false)
	 */
	public function format($data)
	{
		$config = new Format();

		// SimpleXML is installed but default
		// but best to check, and then provide a fallback.
		// SimpleXML 已安装，但默认情况下可能存在问题，建议先检查，然后提供一个备用方案。
		if (! extension_loaded('simplexml'))
		{
			// never thrown in travis-ci
			// @codeCoverageIgnoreStart
			throw FormatException::forMissingExtension();
			// @codeCoverageIgnoreEnd
		}

		$options = $config->formatterOptions['application/xml'] ?? 0;
		$output  = new SimpleXMLElement('<?xml version="1.0"?><response></response>', $options);

		$this->arrayToXML((array) $data, $output);

		return $output->asXML();
	}

	/**
	 * A recursive method to convert an array into a valid XML string.
	 * 将数组转换为有效XML字符串的递归方法。
	 *
	 * Written by CodexWorld. Received permission by email on Nov 24, 2016 to use this code.
	 * 由CodexWorld编写。于2016年11月24日通过邮件获得使用此代码的许可。
	 *
	 * @see http://www.codexworld.com/convert-array-to-xml-in-php/
	 *
	 * @param array            $data
	 * @param SimpleXMLElement $output
	 */
	protected function arrayToXML(array $data, &$output)
	{
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$key     = $this->normalizeXMLTag($key);
				$subnode = $output->addChild("$key");
				$this->arrayToXML($value, $subnode);
			}
			else
			{
				$key = $this->normalizeXMLTag($key);
				$output->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}

	/**
	 * Normalizes tags into the allowed by W3C.
	 * Regex adopted from this StackOverflow answer.
	 * 将标签标准化为 W3C 允许的格式。
	 * 正则表达式参考自 StackOverflow 的这个答案。
	 *
	 * @param string|integer $key
	 *
	 * @return string
	 *
	 * @see https://stackoverflow.com/questions/60001029/invalid-characters-in-xml-tag-name
	 */
	protected function normalizeXMLTag($key)
	{
		$startChar = 'A-Z_a-z' .
			'\\x{C0}-\\x{D6}\\x{D8}-\\x{F6}\\x{F8}-\\x{2FF}\\x{370}-\\x{37D}' .
			'\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}' .
			'\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}' .
			'\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}';
		$validName = $startChar . '\\.\\d\\x{B7}\\x{300}-\\x{36F}\\x{203F}-\\x{2040}';

		$key = trim($key);
		$key = preg_replace("/[^{$validName}-]+/u", '', $key);
		$key = preg_replace("/^[^{$startChar}]+/u", 'item$0', $key);

		return preg_replace('/^(xml).*/iu', 'item$0', $key); // XML is a reserved starting word
	}
}
