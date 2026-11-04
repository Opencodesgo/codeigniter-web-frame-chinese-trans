<?php
/**
 * CodeIgniter，HTTP，消息接口
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
 * Expected behavior of an HTTP request
 * HTTP请求的预期行为。
 */
interface MessageInterface
{
	/**
	 * Sets the body of the current message.
	 * 设置当前消息的正文
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function setBody($data);

	/**
	 * Appends data to the body of the current message.
	 * 将数据追加到当前消息的正文
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public function appendBody($data);

	/**
	 * Populates the $headers array with any headers the getServer knows about.
	 * 用getServer知道的任何头填充$headers数组
	 */
	public function populateHeaders(): void;

	/**
	 * Returns an array containing all Headers.
	 * 返回一个包含所有标头的数组
	 *
	 * @return array<string,Header> An array of the Header objects
	 */
	public function headers(): array;

	/**
	 * Returns a single Header object. If multiple headers with the same
	 * name exist, then will return an array of header objects.
	 * 返回单个 Header 对象。如果存在多个同名的头，将返回一个包含多个 Header 对象的数组。
	 *
	 * @param string $name
	 *
	 * @return array|Header|null
	 */
	public function header($name);

	/**
	 * Sets a header and it's value.
	 * 设置标题和它的值
	 *
	 * @param string            $name
	 * @param array|null|string $value
	 *
	 * @return $this
	 */
	public function setHeader(string $name, $value);

	/**
	 * Removes a header from the list of headers we track.
	 * 从我们跟踪的标头列表中删除一个标头
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function removeHeader(string $name);

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
	public function appendHeader(string $name, ?string $value);

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
	public function prependHeader(string $name, string $value);

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
	public function setProtocolVersion(string $version);
}
