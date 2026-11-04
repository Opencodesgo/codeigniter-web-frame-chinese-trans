<?php
/**
 * CodeIgniter，HTTP，请求接口
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
 * Expected behavior of an HTTP request
 * HTTP请求的预期行为。
 *
 * @mixin \CodeIgniter\HTTP\IncomingRequest
 * @mixin \CodeIgniter\HTTP\CLIRequest
 * @mixin \CodeIgniter\HTTP\CURLRequest
 */
interface RequestInterface
{
	/**
	 * Gets the user's IP address.
	 * Supplied by RequestTrait.
	 * 获取用户的IP地址。由RequestTrait提供。
	 *
	 * @return string IP address
	 */
	public function getIPAddress(): string;

	//--------------------------------------------------------------------

	/**
	 * Validate an IP address
	 * 验证IP地址
	 *
	 * @param string $ip    IP Address
	 * @param string $which IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return boolean
	 *
	 * @deprecated Use Validation instead
	 */
	public function isValidIP(string $ip, string $which = null): bool;

	//--------------------------------------------------------------------

	/**
	 * Get the request method.
	 * An extension of PSR-7's getMethod to allow casing.
	 * 获取请求方法。这是对 PSR-7 的 getMethod 的扩展，支持大小写转换。
	 *
	 * @param boolean $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 *
	 * @deprecated The $upper functionality will be removed and this will revert to its PSR-7 equivalent
	 */
	public function getMethod(bool $upper = false): string;

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the $_SERVER array.
	 * Supplied by RequestTrait.
	 * 从 $_SERVER 数组中获取一个项目。由 RequestTrait 提供。
	 *
	 * @param  string $index  Index for item to be fetched from $_SERVER
	 * @param  null   $filter A filter name to be applied
	 * @return mixed
	 */
	public function getServer($index = null, $filter = null);

	//--------------------------------------------------------------------
}
