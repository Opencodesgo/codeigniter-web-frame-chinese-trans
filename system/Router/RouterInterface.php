<?php
/**
 * CodeIgniter，路由器，路由器接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Router;

use CodeIgniter\HTTP\Request;

/**
 * Expected behavior of a Router.
 * 路由器的预期行为。
 */
interface RouterInterface
{
	/**
	 * Stores a reference to the RouteCollection object.
	 * 存储对RouteCollection对象的引用
	 *
	 * @param RouteCollectionInterface $routes
	 * @param Request                  $request
	 */
	public function __construct(RouteCollectionInterface $routes, Request $request = null);

	//--------------------------------------------------------------------

	/**
	 * Scans the URI and attempts to match the current URI to the
	 * one of the defined routes in the RouteCollection.
	 * 扫描URI，并尝试将当前URI与RouteCollection中定义的路由进行匹配。
	 *
	 * @param string $uri
	 *
	 * @return mixed
	 */
	public function handle(string $uri = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the matched controller.
	 * 返回匹配的控制器的名称
	 *
	 * @return mixed
	 */
	public function controllerName();

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the method to run in the
	 * chosen container.
	 * 返回要在所选容器中执行的方法名称
	 *
	 * @return mixed
	 */
	public function methodName();

	//--------------------------------------------------------------------

	/**
	 * Returns the binds that have been matched and collected
	 * during the parsing process as an array, ready to send to
	 * instance->method(...$params).
	 *
	 * @return mixed
	 */
	public function params();

	//--------------------------------------------------------------------

	/**
	 * Sets the value that should be used to match the index.php file. Defaults
	 * to index.php but this allows you to modify it in case your are using
	 * something like mod_rewrite to remove the page. This allows you to set
	 * it a blank.
	 * 设置用于匹配index.php文件的值。
	 * 默认为 index.php，但如果你使用了类似 mod_rewrite 的功能来移除页面，可以修改它。这样你可以将其设置为空。
	 *
	 * @param string $page
	 *
	 * @return mixed
	 */
	public function setIndexPage($page);

	//--------------------------------------------------------------------
}
