<?php
/**
 * CodeIgniter，过滤器，CSRF
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
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Services;

/**
 * CSRF filter.
 * CSRF 过滤器。
 *
 * This filter is not intended to be used from the command line.
 * 此筛选器不打算从命令行中使用。
 *
 * @codeCoverageIgnore
 */
class CSRF implements FilterInterface
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
	 * 执行此过滤器需要执行的任何处理。
	 * 默认情况下，在正常执行期间不应返回任何内容。
	 * 然而，当检测到异常状态时，应返回一个 CodeIgniter\HTTP\Response 实例。
	 *
	 * @param RequestInterface|IncomingRequest $request
	 * @param array|null                       $arguments
	 *
	 * @return mixed
	 * @throws SecurityException
	 */
	public function before(RequestInterface $request, $arguments = null)
	{
		if ($request->isCLI())
		{
			return;
		}

		$security = Services::security();

		try
		{
			$security->verify($request);
		}
		catch (SecurityException $e)
		{
			if ($security->shouldRedirect() && ! $request->isAJAX())
			{
				return redirect()->back()->with('error', $e->getMessage());
			}

			throw $e;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * We don't have anything to do here.
	 * 我们在这里没什么事可做
	 *
	 * @param RequestInterface|IncomingRequest             $request
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 * @param array|null                                   $arguments
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}

	//--------------------------------------------------------------------
}
