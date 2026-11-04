<?php
/**
 * CodeIgniter，会话，处理程序，基本处理器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use Config\App as AppConfig;
use Psr\Log\LoggerAwareTrait;
use SessionHandlerInterface;

/**
 * Base class for session handling
 * 会话处理的基类。
 */
abstract class BaseHandler implements SessionHandlerInterface
{
	use LoggerAwareTrait;

	/**
	 * The Data fingerprint.
	 * 数据指纹
	 *
	 * @var string
	 */
	protected $fingerprint;

	/**
	 * Lock placeholder.
	 * 锁占位符
	 *
	 * @var mixed
	 */
	protected $lock = false;

	/**
	 * Cookie prefix
	 * Cookie的前缀
	 *
	 * @var string
	 */
	protected $cookiePrefix = '';

	/**
	 * Cookie domain
	 * 跨域Cookie
	 *
	 * @var string
	 */
	protected $cookieDomain = '';

	/**
	 * Cookie path
	 * Cookie路径
	 *
	 * @var string
	 */
	protected $cookiePath = '/';

	/**
	 * Cookie secure?
	 * cookie安全
	 *
	 * @var boolean
	 */
	protected $cookieSecure = false;

	/**
	 * Cookie name to use
	 * 要使用的Cookie名称
	 *
	 * @var string
	 */
	protected $cookieName;

	/**
	 * Match IP addresses for cookies?
	 * 为cookie匹配IP地址？
	 *
	 * @var boolean
	 */
	protected $matchIP = false;

	/**
	 * Current session ID
	 * 当前会话ID
	 *
	 * @var string
	 */
	protected $sessionID;

	/**
	 * The 'save path' for the session
	 * varies between
	 * 会话的“保存路径”因情况而异
	 *
	 * @var string|array
	 */
	protected $savePath;

	/**
	 * User's IP address.
	 * 用户IP地址
	 *
	 * @var string
	 */
	protected $ipAddress;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param AppConfig $config
	 * @param string    $ipAddress
	 */
	public function __construct(AppConfig $config, string $ipAddress)
	{
		$this->cookiePrefix = $config->cookiePrefix;
		$this->cookieDomain = $config->cookieDomain;
		$this->cookiePath   = $config->cookiePath;
		$this->cookieSecure = $config->cookieSecure;
		$this->cookieName   = $config->sessionCookieName;
		$this->matchIP      = $config->sessionMatchIP;
		$this->savePath     = $config->sessionSavePath;
		$this->ipAddress    = $ipAddress;
	}

	//--------------------------------------------------------------------

	/**
	 * Internal method to force removal of a cookie by the client
	 * when session_destroy() is called.
	 * 在调用 session_destroy() 时，强制客户端删除 Cookie 的内部方法。
	 *
	 * @return boolean
	 */
	protected function destroyCookie(): bool
	{
		return setcookie(
				$this->cookieName, '', 1, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, true
		);
	}

	//--------------------------------------------------------------------

	/**
	 * A dummy method allowing drivers with no locking functionality
	 * (databases other than PostgreSQL and MySQL) to act as if they
	 * do acquire a lock.
	 * 一个模拟方法，允许没有锁定功能的驱动程序（即非PostgreSQL和MySQL的数据库）假装获得了锁。
	 *
	 * @param string $sessionID
	 *
	 * @return boolean
	 */
	protected function lockSession(string $sessionID): bool
	{
		$this->lock = true;
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Releases the lock, if any.
	 * 释放锁（如果有的话）
	 *
	 * @return boolean
	 */
	protected function releaseLock(): bool
	{
		$this->lock = false;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Fail
	 * 失败。
	 *
	 * Drivers other than the 'files' one don't (need to) use the
	 * session.save_path INI setting, but that leads to confusing
	 * error messages emitted by PHP when open() or write() fail,
	 * as the message contains session.save_path ...
	 * To work around the problem, the drivers will call this method
	 * so that the INI is set just in time for the error message to
	 * be properly generated.
	 *
	 * @return boolean
	 */
	protected function fail(): bool
	{
		ini_set('session.save_path', $this->savePath);

		return false;
	}
}
