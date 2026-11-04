<?php
/**
 * CodeIgniter，会话，处理程序，数组处理器
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

use Exception;

/**
 * Session handler using static array for storage.
 * Intended only for use during testing.
 * 使用静态数组进行存储的会话处理器。仅用于测试期间。
 */
class ArrayHandler extends BaseHandler
{
	protected static $cache = [];

	//--------------------------------------------------------------------

	/**
	 * Open
	 * 打开。
	 *
	 * Ensures we have an initialized database connection.
	 * 确保我们有一个初始化的数据库连接。
	 *
	 * @param string $savePath Path to session files' directory
	 * @param string $name     Session cookie name
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function open($savePath, $name): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Read
	 * 读取。
	 *
	 * Reads session data and acquires a lock
	 * 读取会话数据并获得锁。
	 *
	 * @param string $sessionID Session ID
	 *
	 * @return string    Serialized session data
	 */
	public function read($sessionID): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Write
	 * 写入。
	 *
	 * Writes (create / update) session data
	 *
	 * @param string $sessionID   Session ID
	 * @param string $sessionData Serialized session data
	 *
	 * @return boolean
	 */
	public function write($sessionID, $sessionData): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Close
	 * 关闭。
	 *
	 * Releases locks and closes file descriptor.
	 * 释放锁并关闭文件描述符。
	 *
	 * @return boolean
	 */
	public function close(): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Destroy
	 * 销毁。
	 *
	 * Destroys the current session.
	 * 销毁当前会话。
	 *
	 * @param string $sessionID
	 *
	 * @return boolean
	 */
	public function destroy($sessionID): bool
	{
		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Garbage Collector
	 * 垃圾回收器。
	 *
	 * Deletes expired sessions
	 * 删除过期会话。
	 *
	 * @param integer $maxlifetime Maximum lifetime of sessions
	 *
	 * @return boolean
	 */
	public function gc($maxlifetime): bool
	{
		return true;
	}

	//--------------------------------------------------------------------
}
