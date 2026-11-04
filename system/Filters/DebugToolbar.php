<?php
/**
 * CodeIgniter，过滤器，调试工具栏
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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Debug toolbar filter
 * 调试工具栏过滤器。
 */
class DebugToolbar implements FilterInterface
{
	/**
	 * We don't need to do anything here.
	 * 我们不需要做任何事
	 *
	 * @param RequestInterface|IncomingRequest $request
	 * @param array|null                       $arguments
	 *
	 * @return void
	 */
	public function before(RequestInterface $request, $arguments = null)
	{
	}

	//--------------------------------------------------------------------

	/**
	 * If the debug flag is set (CI_DEBUG) then collect performance
	 * and debug information and display it in a toolbar.
	 * 如果设置了调试标志（CI_DEBUG），则收集性能和调试信息，并在工具栏中显示。
	 *
	 * @param RequestInterface|IncomingRequest $request
	 * @param ResponseInterface|Response       $response
	 * @param array|null                       $arguments
	 *
	 * @return void
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		Services::toolbar()->prepare($request, $response);
	}

	//--------------------------------------------------------------------
}
