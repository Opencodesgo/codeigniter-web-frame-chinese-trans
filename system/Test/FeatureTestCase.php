<?php
/**
 * CodeIgniter，测试，功能测试用例
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

use CodeIgniter\Router\RouteCollection;

/**
 * Class FeatureTestCase
 * 功能测试用例类。
 *
 * Provides a base class with the trait for doing full HTTP testing
 * against your application.
 * 提供一个基类，用于对您的应用程序进行完整的 HTTP 测试。
 */
class FeatureTestCase extends CIDatabaseTestCase
{
	use FeatureTestTrait;

	/**
	 * If present, will override application
	 * routes when using call().
	 * 如果存在，将覆盖使用 call() 时的应用路由。
	 *
	 * @var RouteCollection
	 */
	protected $routes;

	/**
	 * Values to be set in the SESSION global
	 * before running the test.
	 * 在运行测试前，需在 SESSION 全局中设置的值。
	 *
	 * @var array
	 */
	protected $session = [];

	/**
	 * Enabled auto clean op buffer after request call
	 * 启用请求调用后自动清理op缓冲区
	 *
	 * @var boolean
	 */
	protected $clean = true;

	/**
	 * Custom request's headers
	 * 自定义请求头
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Allows for formatting the request body to what
	 * the controller is going to expect
	 * 允许将请求体格式化为控制器所期望的格式
	 *
	 * @var string
	 */
	protected $bodyFormat = '';

	/**
	 * Allows for directly setting the body to what
	 * it needs to be.
	 * 可直接将身体设置为所需状态
	 *
	 * @var mixed
	 */
	protected $requestBody = '';
}
