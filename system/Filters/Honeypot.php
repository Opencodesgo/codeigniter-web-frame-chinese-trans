<?php
/**
 * CodeIgniter，过滤器，蜜罐
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

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Honeypot filter
 * 蜜罐过滤器。
 */
class Honeypot implements FilterInterface
{
	/**
	 * Checks if Honeypot field is empty; if not
	 * then the requester is a bot
	 * 检查蜜罐字段是否为空；否则，请求者就是机器人。
	 *
	 * @param RequestInterface $request
	 * @param array|null       $arguments
	 *
	 * @return void
	 */
	public function before(RequestInterface $request, $arguments = null)
	{
		$honeypot = Services::honeypot(new \Config\Honeypot());
		if ($honeypot->hasContent($request))
		{
			throw HoneypotException::isBot();
		}
	}

	/**
	 * Attach a honeypot to the current response.
	 * 将蜜罐附加到当前响应
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param array|null        $arguments
	 *
	 * @return void
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		$honeypot = Services::honeypot(new \Config\Honeypot());
		$honeypot->attachHoneypot($response);
	}
}
