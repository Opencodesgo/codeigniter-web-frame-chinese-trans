<?php
/**
 * CodeIgniter，HTTP，数据头
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

/**
 * Class Header
 * 数据头类。
 *
 * Represents a single HTTP header.
 * 表示单个HTTP报头。
 */
class Header
{
	/**
	 * The name of the header.
	 * 头文件的名称
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The value of the header. May have more than one
	 * value. If so, will be an array of strings.
	 * 标题的值。可能有多个值，如果是，则为字符串数组。
	 *
	 * @var string|array
	 */
	protected $value;

	//--------------------------------------------------------------------

	/**
	 * Header constructor. name is mandatory, if a value is provided, it will be set.
	 * 头的构造函数。名称是必选的，如果提供了值，将被设置。
	 *
	 * @param string            $name
	 * @param string|array|null $value
	 */
	public function __construct(string $name, $value = null)
	{
		$this->name = $name;
		$this->setValue($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the header, in the same case it was set.
	 * 在相同的情况下，返回标头的名称。
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the raw value of the header. This may return either a string
	 * of an array, depending on whether the header has multiple values or not.
	 * 获取标头的原始值。根据标头是否具有多个值，该方法可能返回字符串或数组。
	 *
	 * @return array|string
	 */
	public function getValue()
	{
		return $this->value;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the name of the header, overwriting any previous value.
	 * 设置标头的名称，覆盖之前的任何值。
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName(string $name)
	{
		$this->name = $name;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the value of the header, overwriting any previous value(s).
	 * 设置标头的值，覆盖之前的任何值。
	 *
	 * @param string|array|null $value
	 *
	 * @return $this
	 */
	public function setValue($value = null)
	{
		$this->value = $value ?? '';

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Appends a value to the list of values for this header. If the
	 * header is a single value string, it will be converted to an array.
	 * 向此标头的值列表中添加一个值。如果标题是单个值字符串，则会将其转换为数组。
	 *
	 * @param string|array|null $value
	 *
	 * @return $this
	 */
	public function appendValue($value = null)
	{
		if ($value === null)
		{
			return $this;
		}

		if (! is_array($this->value))
		{
			$this->value = [$this->value];
		}

		if (! in_array($value, $this->value, true))
		{
			$this->value[] = $value;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Prepends a value to the list of values for this header. If the
	 * header is a single value string, it will be converted to an array.
	 * 在该标头的值列表前添加一个值。如果标题是单个值字符串，则会将其转换为数组。
	 *
	 * @param string|array|null $value
	 *
	 * @return $this
	 */
	public function prependValue($value = null)
	{
		if ($value === null)
		{
			return $this;
		}

		if (! is_array($this->value))
		{
			$this->value = [$this->value];
		}

		array_unshift($this->value, $value);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves a comma-separated string of the values for a single header.
	 * 检索单个标头的值的逗号分隔字符串。
	 *
	 * NOTE: Not all header values may be appropriately represented using
	 * comma concatenation. For such headers, use getHeader() instead
	 * and supply your own delimiter when concatenating.
	 *
	 * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
	 */
	public function getValueLine(): string
	{
		if (is_string($this->value))
		{
			return $this->value;
		}
		if (! is_array($this->value))
		{
			return '';
		}

		$options = [];

		foreach ($this->value as $key => $value)
		{
			if (is_string($key) && ! is_array($value))
			{
				$options[] = $key . '=' . $value;
			}
			elseif (is_array($value))
			{
				$key       = key($value);
				$options[] = $key . '=' . $value[$key];
			}
			elseif (is_numeric($key))
			{
				$options[] = $value;
			}
		}

		return implode(', ', $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a representation of the entire header string, including
	 * the header name and all values converted to the proper format.
	 * 返回整个标头字符串的表示形式，包括标头名称以及所有值已转换为适当格式的内容。
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->name . ': ' . $this->getValueLine();
	}

	//--------------------------------------------------------------------
}
