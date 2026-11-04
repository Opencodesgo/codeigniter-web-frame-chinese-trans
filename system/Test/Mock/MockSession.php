<?php
/**
 * CodeIgniter，测试，模拟，模拟会话
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Session\Session;

/**
 * Class MockSession
 * 模拟会话类。
 *
 * Provides a safe way to test the Session class itself,
 * that doesn't interact with the session or cookies at all.
 * 提供了一种安全的方式来测试 Session 类本身，且完全不涉及会话或 Cookie。
 */
class MockSession extends Session
{
	/**
	 * Holds our "cookie" data.
	 * 保存我们的“cookie”数据
	 *
	 * @var array
	 */
	public $cookies = [];

	public $didRegenerate = false;

	//--------------------------------------------------------------------

	/**
	 * Sets the driver as the session handler in PHP.
	 * Extracted for easier testing.
	 * 将驱动程序设置为 PHP 的会话处理器。提取用于便于测试。
	 */
	protected function setSaveHandler()
	{
		//        session_set_save_handler($this->driver, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Starts the session.
	 * Extracted for testing reasons.
	 * 开始会话。出于测试目的提取。
	 */
	protected function startSession()
	{
		//        session_start();
		$this->setCookie();
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of setting the cookie on the client side.
	 * Extracted for testing reasons.
	 * 负责在客户端设置 Cookie。出于测试目的提取。
	 */
	protected function setCookie()
	{
		if (PHP_VERSION_ID < 70300)
		{
			$sameSite = '';
			if ($this->cookieSameSite !== '')
			{
				$sameSite = '; samesite=' . $this->cookieSameSite;
			}

			$this->cookies[] = [
				$this->sessionCookieName,
				session_id(),
				empty($this->sessionExpiration) ? 0 : time() + $this->sessionExpiration,
				$this->cookiePath . $sameSite, // Hacky way to set SameSite for PHP 7.2 and earlier
				$this->cookieDomain,
				$this->cookieSecure,
				true,
			];
		}
		else
		{
			// PHP 7.3 adds another function signature allowing setting of samesite
			$params = [
				'expires'  => empty($this->sessionExpiration) ? 0 : time() + $this->sessionExpiration,
				'path'     => $this->cookiePath,
				'domain'   => $this->cookieDomain,
				'secure'   => $this->cookieSecure,
				'httponly' => true,
			];

			if ($this->cookieSameSite !== '')
			{
				$params['samesite'] = $this->cookieSameSite;
			}

			$this->cookies[] = [
				$this->sessionCookieName,
				session_id(),
				$params,
			];
		}
	}

	//--------------------------------------------------------------------

	public function regenerate(bool $destroy = false)
	{
		$this->didRegenerate              = true;
		$_SESSION['__ci_last_regenerate'] = time();
	}

	//--------------------------------------------------------------------
}
