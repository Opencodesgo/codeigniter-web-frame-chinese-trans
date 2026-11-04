<?php
/**
 * CodeIgniter，路由器，异常，路由器异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Router\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * RouterException
 * 路由器异常。
 */
class RouterException extends FrameworkException
{
	/**
	 * Thrown when the actual parameter type does not match
	 * the expected types.
	 * 当实际参数类型与预期类型不匹配时抛出
	 *
	 * @return RouterException
	 */
	public static function forInvalidParameterType()
	{
		return new static(lang('Router.invalidParameterType'));
	}

	/**
	 * Thrown when a default route is not set.
	 * 当没有设置默认路由时抛出
	 *
	 * @return RouterException
	 */
	public static function forMissingDefaultRoute()
	{
		return new static(lang('Router.missingDefaultRoute'));
	}

	/**
	 * Throw when controller or its method is not found.
	 * 当没有找到控制器或其方法时抛出
	 *
	 * @param string $controller
	 * @param string $method
	 *
	 * @return RouterException
	 */
	public static function forControllerNotFound(string $controller, string $method)
	{
		return new static(lang('HTTP.controllerNotFound', [$controller, $method]));
	}

	/**
	 * Throw when route is not valid.
	 * 当路由无效时抛出
	 *
	 * @param string $route
	 *
	 * @return RouterException
	 */
	public static function forInvalidRoute(string $route)
	{
		return new static(lang('HTTP.invalidRoute', [$route]));
	}
}
