<?php
/**
 * CodeIgniter，测试，控制器响应
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Testable response from a controller
 * 来自控制器的可测试响应。
 */
class ControllerResponse
{
	/**
	 * The request.
	 * 请求
	 *
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * The response.
	 * 响应
	 *
	 * @var ResponseInterface
	 */
	protected $response;

	/**
	 * The message payload.
	 * 消息有效负载
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * DOM for the body.
	 * DOM作为主体
	 *
	 * @var DOMParser
	 */
	protected $dom;

	/**
	 * Constructor.
	 * 构造函数
	 */
	public function __construct()
	{
		$this->dom = new DOMParser();
	}

	//--------------------------------------------------------------------
	// Getters / Setters	获得者/设置者
	//--------------------------------------------------------------------

	/**
	 * Set the body & DOM.
	 * 设置主体和DOM
	 *
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody(string $body)
	{
		$this->body = $body;

		if (! empty($body))
		{
			$this->dom = $this->dom->withString($body);
		}

		return $this;
	}

	/**
	 * Retrieve the body.
	 * 找回主体
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Set the request.
	 * 设置请求
	 *
	 * @param RequestInterface $request
	 *
	 * @return $this
	 */
	public function setRequest(RequestInterface $request)
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Set the response.
	 * 设置响应
	 *
	 * @param ResponseInterface $response
	 *
	 * @return $this
	 */
	public function setResponse(ResponseInterface $response)
	{
		$this->response = $response;

		$this->setBody($response->getBody() ?? '');

		return $this;
	}

	/**
	 * Request accessor.
	 * 请求访问器
	 *
	 * @return RequestInterface
	 */
	public function request()
	{
		return $this->request;
	}

	/**
	 * Response accessor.
	 * 响应访问器
	 *
	 * @return ResponseInterface
	 */
	public function response()
	{
		return $this->response;
	}

	//--------------------------------------------------------------------
	// Simple Response Checks	简单回应检查
	//--------------------------------------------------------------------

	/**
	 * Boils down the possible responses into a boolean valid/not-valid
	 * response type.
	 * 将可能的响应简化为布尔类型的有效/无效响应
	 *
	 * @return boolean
	 */
	public function isOK(): bool
	{
		// Only 200 and 300 range status codes
		// are considered valid.
		// 仅200和300范围的状态码被视为有效
		if ($this->response->getStatusCode() >= 400 || $this->response->getStatusCode() < 200)
		{
			return false;
		}

		// Empty bodies are not considered valid.
		if (empty($this->response->getBody()))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns whether or not the Response was a redirect or RedirectResponse
	 * 返回响应是重定向还是RedirectResponse
	 *
	 * @return boolean
	 */
	public function isRedirect(): bool
	{
		return $this->response instanceof RedirectResponse
			|| $this->response->hasHeader('Location')
			|| $this->response->hasHeader('Refresh');
	}

	//--------------------------------------------------------------------
	// Utility	工具
	//--------------------------------------------------------------------

	/**
	 * Forward any unrecognized method calls to our DOMParser instance.
	 * 将任何无法识别的方法调用转发到DOMParser实例
	 *
	 * @param  string $function Method name
	 * @param  mixed  $params   Any method parameters
	 * @return mixed
	 */
	public function __call($function, $params)
	{
		if (method_exists($this->dom, $function))
		{
			return $this->dom->{$function}(...$params);
		}
	}
}
