<?php
/**
 * CodeIgniter，路由器，异常，重定向异常
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

use Exception;

/**
 * RedirectException
 * 重定向的异常。
 */
class RedirectException extends Exception
{
	/**
	 * Status code for redirects
	 * 重定向的状态码
	 *
	 * @var integer
	 */
	protected $code = 302;
}
