<?php
/**
 * CodeIgniter，测试，控制器测试器
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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use InvalidArgumentException;
use Throwable;

/**
 * ControllerTester Trait
 * ControllerTester 特征。
 *
 * Provides features that make testing controllers simple and fluent.
 * 提供使测试控制器简单和流畅的功能。
 *
 * Example:
 *
 *  $this->withRequest($request)
 *       ->withResponse($response)
 *       ->withURI($uri)
 *       ->withBody($body)
 *       ->controller('App\Controllers\Home')
 *       ->run('methodName');
 */
trait ControllerTester
{
	/**
	 * Controller configuration.
	 * 控制器配置
	 *
	 * @var BaseConfig
	 */
	protected $appConfig;

	/**
	 * Request.
	 * 请求
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Response.
	 * 响应
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Message logger.
	 * 消息记录器
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Initialized controller.
	 * 初始化控制器
	 *
	 * @var Controller
	 */
	protected $controller;

	/**
	 * URI of this request.
	 * 此请求的URI
	 *
	 * @var string
	 */
	protected $uri = 'http://example.com';

	/**
	 * Request or response body.
	 * 请求或响应体
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Loads the specified controller, and generates any needed dependencies.
	 * 加载指定的控制器，并生成任何所需的依赖项。
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function controller(string $name)
	{
		if (! class_exists($name))
		{
			throw new InvalidArgumentException('Invalid Controller: ' . $name);
		}

		if (empty($this->appConfig))
		{
			$this->appConfig = new App();
		}

		if (! $this->uri instanceof URI)
		{
			$this->uri = new URI($this->appConfig->baseURL ?? 'http://example.com/');
		}

		if (empty($this->request))
		{
			$this->request = new IncomingRequest($this->appConfig, $this->uri, $this->body, new UserAgent());
		}

		if (empty($this->response))
		{
			$this->response = new Response($this->appConfig);
		}

		if (empty($this->logger))
		{
			$this->logger = Services::logger();
		}

		$this->controller = new $name();
		$this->controller->initController($this->request, $this->response, $this->logger);

		return $this;
	}

	/**
	 * Runs the specified method on the controller and returns the results.
	 * 在控制器上运行指定的方法并返回结果
	 *
	 * @param string $method
	 * @param array  $params
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return ControllerResponse
	 */
	public function execute(string $method, ...$params)
	{
		if (! method_exists($this->controller, $method) || ! is_callable([$this->controller, $method]))
		{
			throw new InvalidArgumentException('Method does not exist or is not callable in controller: ' . $method);
		}

		// The URL helper is always loaded by the system
		// so ensure it's available.
		// URL助手始终由系统加载，因此请确保其可用。
		helper('url');

		$result = (new ControllerResponse())
				->setRequest($this->request)
				->setResponse($this->response);

		$response = null;
		try
		{
			ob_start();

			$response = $this->controller->{$method}(...$params);
		}
		catch (Throwable $e)
		{
			$result->response()->setStatusCode($e->getCode());
		}
		finally
		{
			$output = ob_get_clean();

			// If the controller returned a response, use it
			// 如果控制器返回一个响应，使用它。
			if (isset($response) && $response instanceof Response)
			{
				$result->setResponse($response);
			}

			// check if controller returned a view rather than echoing it
			// 检查控制器是否返回视图,而不是响应它。
			if (is_string($response))
			{
				$output = $response;
				$result->response()->setBody($output);
				$result->setBody($output);
			}
			elseif (! empty($response) && ! empty($response->getBody()))
			{
				$result->setBody($response->getBody());
			}
			else
			{
				$result->setBody('');
			}
		}

		// If not response code has been sent, assume a success
		if (empty($result->response()->getStatusCode()))
		{
			$result->response()->setStatusCode(200);
		}

		return $result;
	}

	/**
	 * Set controller's config, with method chaining.
	 * 使用方法链接设置控制器的配置
	 *
	 * @param mixed $appConfig
	 *
	 * @return mixed
	 */
	public function withConfig($appConfig)
	{
		$this->appConfig = $appConfig;

		return $this;
	}

	/**
	 * Set controller's request, with method chaining.
	 * 设置控制器的请求，使用方法链接。
	 *
	 * @param mixed $request
	 *
	 * @return mixed
	 */
	public function withRequest($request)
	{
		$this->request = $request;

		// Make sure it's available for other classes
		Services::injectMock('request', $request);

		return $this;
	}

	/**
	 * Set controller's response, with method chaining.
	 * 设置控制器的响应，使用方法链接。
	 *
	 * @param mixed $response
	 *
	 * @return mixed
	 */
	public function withResponse($response)
	{
		$this->response = $response;

		return $this;
	}

	/**
	 * Set controller's logger, with method chaining.
	 * 使用方法链接设置控制器的记录器
	 *
	 * @param mixed $logger
	 *
	 * @return mixed
	 */
	public function withLogger($logger)
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Set the controller's URI, with method chaining.
	 * 使用方法链接设置控制器的URI
	 *
	 * @param string $uri
	 *
	 * @return mixed
	 */
	public function withUri(string $uri)
	{
		$this->uri = new URI($uri);

		return $this;
	}

	/**
	 * Set the method's body, with method chaining.
	 * 使用方法链接设置方法的主体
	 *
	 * @param mixed $body
	 *
	 * @return mixed
	 */
	public function withBody($body)
	{
		$this->body = $body;

		return $this;
	}
}
