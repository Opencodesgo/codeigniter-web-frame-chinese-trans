<?php
/**
 * CodeIgniter，安全，安全接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Security;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

/**
 * Expected behavior of a Security.
 * 安全的预期行为。
 */
interface SecurityInterface
{
	/**
	 * CSRF Verify
	 * CSRF验证
	 *
	 * @param RequestInterface $request
	 *
	 * @return $this|false
	 * 
	 * @throws SecurityException
	 */
	public function verify(RequestInterface $request);

	/**
	 * Returns the CSRF Hash.
	 * 返回CSRF哈希值
	 *
	 * @return string|null
	 */
	public function getHash(): ?string;

	/**
	 * Returns the CSRF Token Name.
	 * 返回CSRF令牌名称
	 *
	 * @return string
	 */
	public function getTokenName(): string;

	/**
	 * Returns the CSRF Header Name.
	 * 返回CSRF报头名称
	 *
	 * @return string
	 */
	public function getHeaderName(): string;

	/**
	 * Returns the CSRF Cookie Name.
	 * 返回CSRF Cookie名称
	 *
	 * @return string
	 */
	public function getCookieName(): string;

	/**
	 * Check if CSRF cookie is expired.
	 * 检查CSRF cookie是否过期
	 *
	 * @return boolean
	 */
	public function isExpired(): bool;

	/**
	 * Check if request should be redirect on failure.
	 * 检查请求失败时是否应该重定向
	 *
	 * @return boolean
	 */
	public function shouldRedirect(): bool;

	/**
	 * Sanitize Filename
	 * Sanitize文件名。
	 *
	 * Tries to sanitize filenames in order to prevent directory traversal attempts
	 * and other security threats, which is particularly useful for files that
	 * were supplied via user input.
	 * 尝试对文件名进行消毒处理，以防止目录遍历等安全威胁，这对于通过用户输入提供的文件尤其有用。
	 *
	 * If it is acceptable for the user input to include relative paths,
	 * e.g. file/in/some/approved/folder.txt, you can set the second optional
	 * parameter, $relative_path to TRUE.
	 *
	 * @param string  $str          Input file name
	 * @param boolean $relativePath Whether to preserve paths
	 *
	 * @return string
	 */
	public function sanitizeFilename(string $str, bool $relativePath = false): string;
}
