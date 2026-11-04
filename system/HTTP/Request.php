<?php
/**
 * CodeIgniter，HTTP，请求
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

use CodeIgniter\Validation\FormatRules;

/**
 * Representation of an HTTP request.
 * HTTP请求的表示形式。
 */
class Request extends Message implements MessageInterface, RequestInterface
{
	use RequestTrait;

	/**
	 * Proxy IPs
	 * 代理IPs
	 *
	 * @var string|array
	 *
	 * @deprecated Check the App config directly
	 */
	protected $proxyIPs;

	/**
	 * Request method.
	 * 请求方法
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * A URI instance.
	 * URI实例
	 *
	 * @var URI
	 */
	protected $uri;

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param object $config
	 *
	 * @deprecated The $config is no longer needed and will be removed in a future version
	 */
	public function __construct($config = null)
	{
		/** @deprecated $this->proxyIps property will be removed in the future */
		$this->proxyIPs = $config->proxyIPs;

		if (empty($this->method))
		{
			$this->method = $this->getServer('REQUEST_METHOD') ?? 'GET';
		}

		if (empty($this->uri))
		{
			$this->uri = new URI();
		}
	}

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
	public function isValidIP(string $ip = null, string $which = null): bool
	{
		return (new FormatRules())->valid_ip($ip, $which);
	}

	/**
	 * Get the request method.
	 * 获取请求方法
	 *
	 * @param boolean $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 *
	 * @deprecated The $upper functionality will be removed and this will revert to its PSR-7 equivalent
	 */
	public function getMethod(bool $upper = false): string
	{
		return ($upper) ? strtoupper($this->method) : strtolower($this->method);
	}

	/**
	 * Sets the request method. Used when spoofing the request.
	 * 设置请求方法。在欺骗请求时使用。
	 *
	 * @param string $method
	 *
	 * @return Request
	 *
	 * @deprecated Use withMethod() instead for immutability
	 */
	public function setMethod(string $method)
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Returns an instance with the specified method.
	 * 返回具有指定方法的实例
	 *
	 * @param string $method
	 *
	 * @return static
	 */
	public function withMethod($method)
	{
		$request = clone $this;

		$request->method = $method;

		return $request;
	}

    /**
     * Retrieves the URI instance.
	 * 检索URI实例
     *
     * @return URI
     */
    public function getUri()
	{
		return $this->uri;
	}
}
