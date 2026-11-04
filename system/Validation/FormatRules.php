<?php
/**
 * CodeIgniter，验证，格式规则
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use DateTime;

/**
 * Format validation Rules.
 * 格式验证规则。
 */
class FormatRules
{
	/**
	 * Alpha
	 * 阿尔法
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function alpha(?string $str = null): bool
	{
		return ctype_alpha($str);
	}

	/**
	 * Alpha with spaces.
	 * 带空格的
	 *
	 * @param string|null $value Value.
	 *
	 * @return boolean True if alpha with spaces, else false.
	 */
	public function alpha_space(?string $value = null): bool
	{
		if ($value === null)
		{
			return true;
		}

		// @see https://regex101.com/r/LhqHPO/1
		return (bool) preg_match('/\A[A-Z ]+\z/i', $value);
	}

	/**
	 * Alphanumeric with underscores and dashes
	 * 带有下划线和破折号的字母数字
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function alpha_dash(?string $str = null): bool
	{
		// @see https://regex101.com/r/XfVY3d/1
		return (bool) preg_match('/\A[a-z0-9_-]+\z/i', $str);
	}

	/**
	 * Alphanumeric, spaces, and a limited set of punctuation characters.
	 * Accepted punctuation characters are: ~ tilde, ! exclamation,
	 * # number, $ dollar, % percent, & ampersand, * asterisk, - dash,
	 * _ underscore, + plus, = equals, | vertical bar, : colon, . period
	 * ~ ! # $ % & * - _ + = | : .
	 * 字母数字、空格和一组有限的标点符号。
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function alpha_numeric_punct($str)
	{
		// @see https://regex101.com/r/6N8dDY/1
		return (bool) preg_match('/\A[A-Z0-9 ~!#$%\&\*\-_+=|:.]+\z/i', $str);
	}

	/**
	 * Alphanumeric
	 * 字母数字式
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function alpha_numeric(?string $str = null): bool
	{
		return ctype_alnum($str);
	}

	/**
	 * Alphanumeric w/ spaces
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function alpha_numeric_space(?string $str = null): bool
	{
		// @see https://regex101.com/r/0AZDME/1
		return (bool) preg_match('/\A[A-Z0-9 ]+\z/i', $str);
	}

	/**
	 * Any type of string
	 * 任何类型的字符串。
	 *
	 * Note: we specifically do NOT type hint $str here so that
	 * it doesn't convert numbers into strings.
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function string($str = null): bool
	{
		return is_string($str);
	}

	/**
	 * Decimal number
	 * 十进制数
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function decimal(?string $str = null): bool
	{
		// @see https://regex101.com/r/HULifl/1/
		return (bool) preg_match('/\A[-+]?[0-9]{0,}\.?[0-9]+\z/', $str);
	}

	/**
	 * String of hexidecimal characters
	 * 十六进制字符串
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function hex(?string $str = null): bool
	{
		return ctype_xdigit($str);
	}

	/**
	 * Integer
	 * 整数
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function integer(?string $str = null): bool
	{
		return (bool) preg_match('/\A[\-+]?[0-9]+\z/', $str);
	}

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 * 是一个自然数（0、1、2、3等）
	 *
	 * @param  string|null $str
	 * @return boolean
	 */
	public function is_natural(?string $str = null): bool
	{
		return ctype_digit($str);
	}

	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 * 是自然数，但不是零（1、2、3等）。
	 *
	 * @param  string|null $str
	 * @return boolean
	 */
	public function is_natural_no_zero(?string $str = null): bool
	{
		return ($str !== '0' && ctype_digit($str));
	}

	/**
	 * Numeric
	 * 数字
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function numeric(?string $str = null): bool
	{
		// @see https://regex101.com/r/bb9wtr/1
		return (bool) preg_match('/\A[\-+]?[0-9]*\.?[0-9]+\z/', $str);
	}

	/**
	 * Compares value against a regular expression pattern.
	 * 与正则表达式模式比较值
	 *
	 * @param string|null $str
	 * @param string      $pattern
	 *
	 * @return boolean
	 */
	public function regex_match(?string $str, string $pattern): bool
	{
		if (strpos($pattern, '/') !== 0)
		{
			$pattern = "/{$pattern}/";
		}

		return (bool) preg_match($pattern, $str);
	}

	/**
	 * Validates that the string is a valid timezone as per the
	 * timezone_identifiers_list function.
	 * 验证该字符串是否符合 timezone_identifiers_list 函数所定义的有效时区
	 *
	 * @see http://php.net/manual/en/datetimezone.listidentifiers.php
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function timezone(string $str = null): bool
	{
		return in_array($str, timezone_identifiers_list(), true);
	}

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @param  string $str
	 * @return boolean
	 */
	public function valid_base64(string $str = null): bool
	{
		return (base64_encode(base64_decode($str, true)) === $str);
	}

	/**
	 * Valid JSON
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_json(string $str = null): bool
	{
		json_decode($str);
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Checks for a correctly formatted email address
	 * 检查格式是否正确的电子邮件地址
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_email(string $str = null): bool
	{
		// @see https://regex101.com/r/wlJG1t/1/
		if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $str, $matches))
		{
			$str = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
		}

		return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validate a comma-separated list of email addresses.
	 * 验证以逗号分隔的电子邮件地址列表。
	 *
	 * Example:
	 *     valid_emails[one@example.com,two@example.com]
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_emails(string $str = null): bool
	{
		foreach (explode(',', $str) as $email)
		{
			$email = trim($email);
			if ($email === '')
			{
				return false;
			}

			if ($this->valid_email($email) === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate an IP address (human readable format or binary string - inet_pton)
	 * 验证一个IP地址(人类可读格式或二进制字符串- inet_pton)
	 *
	 * @param string $ip    IP Address
	 * @param string $which IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return boolean
	 */
	public function valid_ip(string $ip = null, string $which = null): bool
	{
		if (empty($ip))
		{
			return false;
		}
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = null;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which) || (! ctype_print($ip) && (bool) filter_var(inet_ntop($ip), FILTER_VALIDATE_IP, $which));
	}

	/**
	 * Checks a URL to ensure it's formed correctly.
	 * 检查URL以确保其格式正确
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_url(string $str = null): bool
	{
		if (empty($str))
		{
			return false;
		}

		if (preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches))
		{
			if (! in_array($matches[1], ['http', 'https'], true))
			{
				return false;
			}

			$str = $matches[2];
		}

		$str = 'http://' . $str;

		return (filter_var($str, FILTER_VALIDATE_URL) !== false);
	}

	/**
	 * Checks for a valid date and matches a given date format
	 * 检查有效的日期并匹配给定的日期格式
	 *
	 * @param string $str
	 * @param string $format
	 *
	 * @return boolean
	 */
	public function valid_date(string $str = null, string $format = null): bool
	{
		if (empty($format))
		{
			return (bool) strtotime($str);
		}

		$date = DateTime::createFromFormat($format, $str);

		return (bool) $date && DateTime::getLastErrors()['warning_count'] === 0 && DateTime::getLastErrors()['error_count'] === 0;
	}
}
