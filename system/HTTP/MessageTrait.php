<?php
/**
 * CodeIgniter，HTTP，信息特征
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

use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * Message Trait
 * Additional methods to make a PSR-7 Message class
 * compliant with the framework's own MessageInterface.
 * 信息特征。
 * 使 PSR-7 消息类符合框架自身 MessageInterface 的其他方法。
 *
 * @see https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php
 */
trait MessageTrait
{
	/**
	 * List of all HTTP request headers.
	 * 所有HTTP请求头的列表
	 *
	 * @var array<string,Header>
	 */
	protected $headers = [];

	/**
	 * Holds a map of lower-case header names
	 * and their normal-case key as it is in $headers.
	 * Used for case-insensitive header access.
	 * 存储小写标题名称及其在 $headers 中的正常大小写键值。
	 * 用于不区分大小写的标题访问。
	 *
	 * @var array
	 */
	protected $headerMap = [];

	//--------------------------------------------------------------------
	// Body	主体
	//--------------------------------------------------------------------

	/**
	 * Sets the body of the current message.
	 * 设置当前消息的正文
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function setBody($data): self
	{
		$this->body = $data;

		return $this;
	}

	/**
	 * Appends data to the body of the current message.
	 * 将数据追加到当前消息的正文
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function appendBody($data): self
	{
		$this->body .= (string) $data;

		return $this;
	}

	//--------------------------------------------------------------------
	// Headers	头文件
	//--------------------------------------------------------------------

	/**
	 * Populates the $headers array with any headers the getServer knows about.
	 * 用getServer知道的任何头填充$headers数组
	 */
	public function populateHeaders(): void
	{
		$contentType = $_SERVER['CONTENT_TYPE'] ?? getenv('CONTENT_TYPE');
		if (! empty($contentType))
		{
			$this->setHeader('Content-Type', $contentType);
		}
		unset($contentType);

		foreach ($_SERVER as $key => $val)
		{
			if (sscanf($key, 'HTTP_%s', $header) === 1)
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace('_', ' ', strtolower($header));
				$header = str_replace(' ', '-', ucwords($header));

				$this->setHeader($header, $_SERVER[$key]);

				// Add us to the header map so we can find them case-insensitively
				$this->headerMap[strtolower($header)] = $header;
			}
		}
	}

	/**
	 * Returns an array containing all Headers.
	 * 返回一个包含所有标头的数组
	 *
	 * @return array<string,Header> An array of the Header objects
	 */
	public function headers(): array
	{
		// If no headers are defined, but the user is
		// requesting it, then it's likely they want
		// it to be populated so do that...
		if (empty($this->headers))
		{
			$this->populateHeaders();
		}

		return $this->headers;
	}

	/**
	 * Returns a single Header object. If multiple headers with the same
	 * name exist, then will return an array of header objects.
	 * 返回单个 Header 对象。如果存在多个同名的头，将返回一个包含多个 Header 对象的数组。
	 *
	 * @param string $name
	 *
	 * @return array|Header|null
	 */
	public function header($name)
	{
		$origName = $this->getHeaderName($name);

		return $this->headers[$origName] ?? null;
	}

	/**
	 * Sets a header and it's value.
	 * 设置标题和它的值
	 *
	 * @param string            $name
	 * @param array|null|string $value
	 *
	 * @return $this
	 */
	public function setHeader(string $name, $value): self
	{
		$origName = $this->getHeaderName($name);

		if (isset($this->headers[$origName]) && is_array($this->headers[$origName]->getValue()))
		{
			if (! is_array($value))
			{
				$value = [$value];
			}

			foreach ($value as $v)
			{
				$this->appendHeader($origName, $v);
			}
		}
		else
		{
			$this->headers[$origName]               = new Header($origName, $value);
			$this->headerMap[strtolower($origName)] = $origName;
		}

		return $this;
	}

	/**
	 * Removes a header from the list of headers we track.
	 * 从我们跟踪的标头列表中删除一个标头
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function removeHeader(string $name): self
	{
		$origName = $this->getHeaderName($name);

		unset($this->headers[$origName]);
		unset($this->headerMap[strtolower($name)]);

		return $this;
	}

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 * 为任何接受多个值的标头（即数组或实现 ArrayAccess 接口的对象）添加额外的标头值
	 *
	 * @param string      $name
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function appendHeader(string $name, ?string $value): self
	{
		$origName = $this->getHeaderName($name);

		array_key_exists($origName, $this->headers)
			? $this->headers[$origName]->appendValue($value)
			: $this->setHeader($name, $value);

		return $this;
	}

	/**
	 * Adds an additional header value to any headers that accept
	 * multiple values (i.e. are an array or implement ArrayAccess)
	 * 为任何接受多个值的标头（即数组或实现 ArrayAccess 接口的对象）添加额外的标头值
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function prependHeader(string $name, string $value): self
	{
		$origName = $this->getHeaderName($name);

		$this->headers[$origName]->prependValue($value);

		return $this;
	}

	/**
	 * Takes a header name in any case, and returns the
	 * normal-case version of the header.
	 * 接受任意情况下的标题名称，并返回该标题的正常大小写形式。
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function getHeaderName(string $name): string
	{
		return $this->headerMap[strtolower($name)] ?? $name;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the HTTP protocol version.
	 * 设置HTTP协议版本
	 *
	 * @param string $version
	 *
	 * @return $this
	 *
	 * @throws HTTPException For invalid protocols
	 */
	public function setProtocolVersion(string $version): self
	{
		if (! is_numeric($version))
		{
			$version = substr($version, strpos($version, '/') + 1);
		}

		// Make sure that version is in the correct format
		$version = number_format((float) $version, 1);

		if (! in_array($version, $this->validProtocolVersions, true))
		{
			throw HTTPException::forInvalidHTTPProtocol(implode(', ', $this->validProtocolVersions));
		}

		$this->protocolVersion = $version;

		return $this;
	}
}
