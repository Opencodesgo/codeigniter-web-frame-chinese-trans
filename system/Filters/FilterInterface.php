<?php
/**
 * CodeIgniter，过滤器，过滤器接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter interface
 * 过滤器接口。
 */
interface FilterInterface
{
	/**
	 * Do whatever processing this filter needs to do.
	 * By default it should not return anything during
	 * normal execution. However, when an abnormal state
	 * is found, it should return an instance of
	 * CodeIgniter\HTTP\Response. If it does, script
	 * execution will end and that Response will be
	 * sent back to the client, allowing for error pages,
	 * redirects, etc.
	 * 执行此过滤器所需的任何处理。默认情况下，在正常执行期间不应返回任何内容。
	 * 但是，当发现异常状态时，应返回一个 CodeIgniter\HTTP\Response 实例。
	 * 如果发生这种情况，脚本执行将结束，并且该响应将被发送回客户端，从而实现错误页面、重定向等功能。
	 *
	 * @param RequestInterface $request
	 * @param null             $arguments
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request, $arguments = null);

	//--------------------------------------------------------------------

	/**
	 * Allows After filters to inspect and modify the response
	 * object as needed. This method does not allow any way
	 * to stop execution of other after filters, short of
	 * throwing an Exception or Error.
	 * 允许 After 过滤器根据需要检查和修改响应对象。
	 * 此方法除了抛出异常或错误外，无法以任何方式停止其他后置过滤器的执行。
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param null              $arguments
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null);

	//--------------------------------------------------------------------
}
