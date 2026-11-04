<?php
/**
 * CodeIgniter，加密，加密接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption;

use CodeIgniter\Encryption\Exceptions\EncryptionException;

/**
 * CodeIgniter Encryption Handler
 * CodeIgniter 加密处理程序。
 *
 * Provides two-way keyed encryption
 * 提供双向密钥加密。
 */
interface EncrypterInterface
{
	/**
	 * Encrypt - convert plaintext into ciphertext
	 * 加密-将明文转换为密文
	 *
	 * @param string            $data   Input data
	 * @param array|string|null $params Overridden parameters, specifically the key
	 *
	 * @throws EncryptionException
	 *
	 * @return string
	 */
	public function encrypt($data, $params = null);

	/**
	 * Decrypt - convert ciphertext into plaintext
	 * 解密-将密文转换为明文
	 *
	 * @param string            $data   Encrypted data
	 * @param array|string|null $params Overridden parameters, specifically the key
	 *
	 * @throws EncryptionException
	 *
	 * @return string
	 */
	public function decrypt($data, $params = null);
}
