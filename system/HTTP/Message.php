<?php
/**
 * CodeIgniter，HTTP，消息
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
 * An HTTP message
 * HTTP消息。
 */
class Message implements MessageInterface
{
	use MessageTrait;

	/**
	 * Protocol version
	 * 协议版本
	 *
	 * @var string
	 */
	protected $protocolVersion;

	/**
	 * List of valid protocol versions
	 * 有效的协议版本列表
	 *
	 * @var array
	 */
	protected $validProtocolVersions = [
		'1.0',
		'1.1',
		'2.0',
	];

	/**
	 * Message body
	 * 消息正文
	 *
	 * @var mixed
	 */
	protected $body;

	//--------------------------------------------------------------------

	/**
	 * Returns the Message's body.
	 * 返回消息体
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Returns an array containing all headers.
	 * 返回一个包含所有标头的数组
	 *
	 * @return array<string,Header> An array of the request headers
	 *
	 * @deprecated Use Message::headers() to make room for PSR-7
	 */
	public function getHeaders(): array
	{
		return $this->headers();
	}

	/**
	 * Returns a single header object. If multiple headers with the same
	 * name exist, then will return an array of header objects.
	 * 返回单个标头对象。如果存在多个同名的标头，则返回一个标头对象数组。
	 *
	 * @param string $name
	 *
	 * @return array|Header|null
	 *
	 * @deprecated Use Message::header() to make room for PSR-7
	 */
	public function getHeader(string $name)
	{
		return $this->header($name);
	}

	/**
	 * Determines whether a header exists.
	 * 确定标头是否存在
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasHeader(string $name): bool
	{
		$origName = $this->getHeaderName($name);

		return isset($this->headers[$origName]);
	}

	/**
	 * Retrieves a comma-separated string of the values for a single header.
	 * 检索单个标头的值的逗号分隔字符串。
	 *
	 * This method returns all of the header values of the given
	 * case-insensitive header name as a string concatenated together using
	 * a comma.
	 * 此方法返回指定的不区分大小写的标头名称的所有值，以逗号连接成一个字符串。
	 *
	 * NOTE: Not all header values may be appropriately represented using
	 * comma concatenation. For such headers, use getHeader() instead
	 * and supply your own delimiter when concatenating.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getHeaderLine(string $name): string
	{
		$origName = $this->getHeaderName($name);

		if (! array_key_exists($origName, $this->headers))
		{
			return '';
		}

		return $this->headers[$origName]->getValueLine();
	}

	/**
	 * Returns the HTTP Protocol Version.
	 * 返回HTTP协议版本
	 *
	 * @return string
	 */
	public function getProtocolVersion(): string
	{
		return $this->protocolVersion ?? '1.1';
	}

	/**
	 * Determines if this is a json message based on the Content-Type header
	 * 根据Content-Type报头确定这是否是json消息
	 *
	 * @return boolean
	 *
	 * @deprecated Use header calls directly
	 */
      public function isJSON()
	{
		if (! $this->hasHeader('Content-Type'))
		{
			return false;
		}

		$header = $this->header('Content-Type')->getValue();
		$parts  = explode(';', $header);

		return in_array('application/json', $parts, true);
	}
}
