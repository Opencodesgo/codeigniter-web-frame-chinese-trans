<?php
/**
 * App，控制器，基本控制器
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 * 基本控制器。
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 * BaseController 为加载组件以及执行所有控制器所需的功能提供了一个便捷的位置。
 *
 * For security be sure to declare any new methods as protected or private.
 * 为了安全起见，请确保将任何新方法声明为受保护或私有。
 */

class BaseController extends Controller
{
	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 * 在类实例化时自动加载的一系列辅助工具。
	 * 这些助手将对所有继承 BaseController 的其他控制器可用。
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		// 不要编辑这一行
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		// 在这里预加载任何模型、库等。
		//--------------------------------------------------------------------
		// E.g.: $this->session = \Config\Services::session();
	}
}
