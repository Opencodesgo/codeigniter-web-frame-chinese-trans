<?php
/**
 * CodeIgniter，加密，处理程序，Sodium 处理器
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
 * SodiumHandler uses libsodium in encryption.
 * SodiumHandler使用libsodium进行加密。
 *
 * @see https://github.com/jedisct1/libsodium/issues/392
 */
class SodiumHandler extends BaseHandler
{
	/**
	 * Starter key
	 * 启动器密钥
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * Block size for padding message.
	 * 填充消息的块大小
	 *
	 * @var integer
	 */
	protected $blockSize = 16;

	/**
	 * {@inheritDoc}
	 */
	public function encrypt($data, $params = null)
	{
		$this->parseParams($params);

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		// create a nonce for this operation
		// 为此操作创建一个nonce
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // 24 bytes

		// add padding before we encrypt the data
		// 在加密数据之前添加填充
		if ($this->blockSize <= 0)
		{
			throw EncryptionException::forEncryptionFailed();
		}

		$data = sodium_pad($data, $this->blockSize);

		// encrypt message and combine with nonce
		$ciphertext = $nonce . sodium_crypto_secretbox($data, $nonce, $this->key);

		// cleanup buffers
		sodium_memzero($data);
		sodium_memzero($this->key);

		return $ciphertext;
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrypt($data, $params = null)
	{
		$this->parseParams($params);

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		if (mb_strlen($data, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES))
		{
			// message was truncated
			throw EncryptionException::forAuthenticationFailed();
		}

		// Extract info from encrypted data
		// 从加密数据中提取信息
		$nonce      = self::substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = self::substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null);

		// decrypt data
		$data = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

		if ($data === false)
		{
			// message was tampered in transit
			throw EncryptionException::forAuthenticationFailed(); // @codeCoverageIgnore
		}

		// remove extra padding during encryption
		// 删除加密期间的额外填充
		if ($this->blockSize <= 0)
		{
			throw EncryptionException::forAuthenticationFailed();
		}

		$data = sodium_unpad($data, $this->blockSize);

		// cleanup buffers
		// 清除缓冲区
		sodium_memzero($ciphertext);
		sodium_memzero($this->key);

		return $data;
	}

	/**
	 * Parse the $params before doing assignment.
	 * 在赋值前解析$params
	 *
	 * @param array|string|null $params
	 *
	 * @throws EncryptionException If key is empty
	 *
	 * @return void
	 */
	protected function parseParams($params)
	{
		if ($params === null)
		{
			return;
		}

		if (is_array($params))
		{
			if (isset($params['key']))
			{
				$this->key = $params['key'];
			}

			if (isset($params['blockSize']))
			{
				$this->blockSize = $params['blockSize'];
			}

			return;
		}

		$this->key = (string) $params;
	}
}
