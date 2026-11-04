<?php
/**
 * CodeIgniter，路由器，路由采集接口
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

use Closure;

/**
 * Interface RouteCollectionInterface
 * 路由采集接口。
 *
 * A Route Collection's sole job is to hold a series of routes. The required
 * number of methods is kept very small on purpose, but implementors may
 * add a number of additional methods to customize how the routes are defined.
 * 路由集合的唯一工作是保存一系列路由。
 *
 * The RouteCollection provides the Router with the routes so that it can determine
 * which controller should be ran.
 */
interface RouteCollectionInterface
{
	/**
	 * Adds a single route to the collection.
	 * 向集合添加单个路由
	 *
	 * @param string       $from
	 * @param array|string $to
	 * @param array        $options
	 *
	 * @return mixed
	 */
	public function add(string $from, $to, array $options = null);

	//--------------------------------------------------------------------

	/**
	 * Registers a new constraint with the system. Constraints are used
	 * by the routes as placeholders for regular expressions to make defining
	 * the routes more human-friendly.
	 * 向系统注册一个新约束。
	 *
	 * You can pass an associative array as $placeholder, and have
	 * multiple placeholders added at once.
	 * 您可以将关联数组传递为$ placeholder,并同时添加多个占位符。
	 *
	 * @param string|array $placeholder
	 * @param string       $pattern
	 *
	 * @return mixed
	 */
	public function addPlaceholder($placeholder, string $pattern = null);

	//--------------------------------------------------------------------

	/**
	 * Sets the default namespace to use for Controllers when no other
	 * namespace has been specified.
	 * 当未指定其他命名空间时，将控制器的默认命名空间设置为该值。
	 *
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function setDefaultNamespace(string $value);

	//--------------------------------------------------------------------

	/**
	 * Sets the default controller to use when no other controller has been
	 * specified.
	 * 当未指定其他控制器时，设置默认使用的控制器。
	 *
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function setDefaultController(string $value);

	//--------------------------------------------------------------------

	/**
	 * Sets the default method to call on the controller when no other
	 * method has been set in the route.
	 * 当路由中未设置其他方法时，将默认调用控制器的此方法。
	 *
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function setDefaultMethod(string $value);

	//--------------------------------------------------------------------

	/**
	 * Tells the system whether to convert dashes in URI strings into
	 * underscores. In some search engines, including Google, dashes
	 * create more meaning and make it easier for the search engine to
	 * find words and meaning in the URI for better SEO. But it
	 * doesn't work well with PHP method names....
	 * 告诉系统是否将URI字符串中的连字符转换为下划线。
	 *
	 * @param boolean $value
	 *
	 * @return mixed
	 */
	public function setTranslateURIDashes(bool $value);

	//--------------------------------------------------------------------

	/**
	 * If TRUE, the system will attempt to match the URI against
	 * Controllers by matching each segment against folders/files
	 * in APPPATH/Controllers, when a match wasn't found against
	 * defined routes.
	 *
	 * If FALSE, will stop searching and do NO automatic routing.
	 *
	 * @param boolean $value
	 *
	 * @return RouteCollectionInterface
	 */
	public function setAutoRoute(bool $value): self;

	//--------------------------------------------------------------------

	/**
	 * Sets the class/method that should be called if routing doesn't
	 * find a match. It can be either a closure or the controller/method
	 * name exactly like a route is defined: Users::index
	 * 设置路由未找到匹配项时应调用的类/方法
	 *
	 * This setting is passed to the Router class and handled there.
	 *
	 * @param callable|null $callable
	 *
	 * @return RouteCollectionInterface
	 */
	public function set404Override($callable = null): self;

	//--------------------------------------------------------------------

	/**
	 * Returns the 404 Override setting, which can be null, a closure
	 * or the controller/string.
	 * 返回404覆盖设置,它可以是null、闭包或控制器/字符串。
	 *
	 * @return string|Closure|null
	 */
	public function get404Override();

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default controller. With Namespace.
	 * 返回默认控制器的名称。与名称空间。
	 *
	 * @return string
	 */
	public function getDefaultController();

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the default method to use within the controller.
	 * 返回要在控制器中使用的默认方法的名称
	 *
	 * @return string
	 */
	public function getDefaultMethod();

	//--------------------------------------------------------------------

	/**
	 * Returns the current value of the translateURIDashes setting.
	 * 返回translateURIDashes设置的当前值
	 *
	 * @return mixed
	 */
	public function shouldTranslateURIDashes();

	//--------------------------------------------------------------------

	/**
	 * Returns the flag that tells whether to autoRoute URI against Controllers.
	 * 返回告诉是否针对控制器自动路由URI的标志
	 *
	 * @return boolean
	 */
	public function shouldAutoRoute();

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of available routes.
	 * 返回可用路由的原始数组
	 *
	 * @return mixed
	 */
	public function getRoutes();

	//--------------------------------------------------------------------

	/**
	 * Returns the current HTTP Verb being used.
	 * 返回正在使用的当前HTTP谓词
	 *
	 * @return string
	 */
	public function getHTTPVerb();

	//--------------------------------------------------------------------

	/**
	 * Attempts to look up a route based on it's destination.
	 * 尝试根据目的地查找路由。
	 *
	 * If a route exists:
	 *
	 *      'path/(:any)/(:any)' => 'Controller::method/$1/$2'
	 *
	 * This method allows you to know the Controller and method
	 * and get the route that leads to it.
	 *
	 *      // Equals 'path/$param1/$param2'
	 *      reverseRoute('Controller::method', $param1, $param2);
	 *
	 * @param string $search
	 * @param array  ...$params
	 *
	 * @return string|false
	 */
	public function reverseRoute(string $search, ...$params);

	//--------------------------------------------------------------------

	/**
	 * Determines if the route is a redirecting route.
	 * 确定路由是否为重定向路由
	 *
	 * @param string $from
	 *
	 * @return boolean
	 */
	public function isRedirect(string $from): bool;

	//--------------------------------------------------------------------

	/**
	 * Grabs the HTTP status code from a redirecting Route.
	 * 从重定向路由中获取HTTP状态码
	 *
	 * @param string $from
	 *
	 * @return integer
	 */
	public function getRedirectCode(string $from): int;

	//--------------------------------------------------------------------
}
