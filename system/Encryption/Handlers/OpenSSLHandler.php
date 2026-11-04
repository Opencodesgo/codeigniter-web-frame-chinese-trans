<?php
/**
 * CodeIgniter，加密，处理程序，OpenSSL 处理程序
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Encryption\Handlers;

use CodeIgniter\Encryption\Exceptions\EncryptionException;

/**
 * Encryption handling for OpenSSL library
 * OpenSSL库的加密处理。
 */
class OpenSSLHandler extends BaseHandler
{
	/**
	 * HMAC digest to use
	 * HMAC摘要使用
	 *
	 * @var string
	 */
	protected $digest = 'SHA512';

	/**
	 * Cipher to use
	 * 使用密码
	 *
	 * @var string
	 */
	protected $cipher = 'AES-256-CTR';

	/**
	 * Starter key
	 * 启动密钥
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * {@inheritDoc}
	 */
	public function encrypt($data, $params = null)
	{
		// Allow key override
		if ($params)
		{
			if (is_array($params) && isset($params['key']))
			{
				$this->key = $params['key'];
			}
			else
			{
				$this->key = $params;
			}
		}

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		// derive a secret key
		$secret = \hash_hkdf($this->digest, $this->key);

		// basic encryption
		$iv = ($ivSize = \openssl_cipher_iv_length($this->cipher)) ? \openssl_random_pseudo_bytes($ivSize) : null;

		$data = \openssl_encrypt($data, $this->cipher, $secret, OPENSSL_RAW_DATA, $iv);

		if ($data === false)
		{
			throw EncryptionException::forEncryptionFailed();
		}

		$result = $iv . $data;

		$hmacKey = \hash_hmac($this->digest, $result, $secret, true);

		return $hmacKey . $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrypt($data, $params = null)
	{
		// Allow key override
		if ($params)
		{
			if (is_array($params) && isset($params['key']))
			{
				$this->key = $params['key'];
			}
			else
			{
				$this->key = $params;
			}
		}

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		// derive a secret key
		// 推出秘密密钥
		$secret = \hash_hkdf($this->digest, $this->key);

		$hmacLength = self::substr($this->digest, 3) / 8;
		$hmacKey    = self::substr($data, 0, $hmacLength);
		$data       = self::substr($data, $hmacLength);
		$hmacCalc   = \hash_hmac($this->digest, $data, $secret, true);

		if (! hash_equals($hmacKey, $hmacCalc))
		{
			throw EncryptionException::forAuthenticationFailed();
		}

		if ($ivSize = \openssl_cipher_iv_length($this->cipher))
		{
			$iv   = self::substr($data, 0, $ivSize);
			$data = self::substr($data, $ivSize);
		}
		else
		{
			$iv = null;
		}

		return \openssl_decrypt($data, $this->cipher, $secret, OPENSSL_RAW_DATA, $iv);
	}
}
