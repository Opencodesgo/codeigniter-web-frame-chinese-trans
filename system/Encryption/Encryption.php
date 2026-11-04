<?php
/**
 * CodeIgniter，加密，Encryption
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
use Config\Encryption as EncryptionConfig;

/**
 * CodeIgniter Encryption Manager
 * CodeIgniter加密管理器。
 *
 * Provides two-way keyed encryption via PHP's Sodium and/or OpenSSL extensions.
 * This class determines the driver, cipher, and mode to use, and then
 * initializes the appropriate encryption handler.
 * 通过 PHP 的 Sodium 和/或 OpenSSL 扩展提供双向密钥加密。
 * 该类会确定要使用的驱动程序、加密算法和模式，然后初始化相应的加密处理程序。
 */
class Encryption
{
	/**
	 * The encrypter we create
	 * 我们创建的加密器
	 *
	 * @var EncrypterInterface
	 */
	protected $encrypter;

	/**
	 * The driver being used
	 * 被使用的驱动程序
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * The key/seed being used
	 * 正在使用的键/种子
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The derived HMAC key
	 * 派生的HMAC密钥
	 *
	 * @var string
	 */
	protected $hmacKey;

	/**
	 * HMAC digest to use
	 * HMAC摘要使用
	 *
	 * @var string
	 */
	protected $digest = 'SHA512';

	/**
	 * Map of drivers to handler classes, in preference order
	 * 驱动程序到处理程序类的映射，按优先顺序排列。
	 *
	 * @var array
	 */
	protected $drivers = [
		'OpenSSL',
		'Sodium',
	];

	/**
	 * Handlers that are to be installed
	 * 要安装的处理程序
	 *
	 * @var array<string, boolean>
	 */
	protected $handlers = [];

	/**
	 * Class constructor
	 * 类构造器
	 *
	 * @param EncryptionConfig $config Configuration parameters
	 *
	 * @throws EncryptionException
	 *
	 * @return void
	 */
	public function __construct(EncryptionConfig $config = null)
	{
		$config = $config ?? new EncryptionConfig();

		$this->key    = $config->key;
		$this->driver = $config->driver;
		$this->digest = $config->digest ?? 'SHA512';

		// Map what we have installed
		$this->handlers = [
			'OpenSSL' => extension_loaded('openssl'),
			// the SodiumHandler uses some API (like sodium_pad) that is available only on v1.0.14+
			'Sodium'  => extension_loaded('sodium') && version_compare(SODIUM_LIBRARY_VERSION, '1.0.14', '>='),
		];

		// If requested driver is not active, bail
		if (! in_array($this->driver, $this->drivers, true) || (array_key_exists($this->driver, $this->handlers) && ! $this->handlers[$this->driver]))
		{
			// this should never happen in travis-ci
			throw EncryptionException::forNoHandlerAvailable($this->driver);
		}
	}

	/**
	 * Initialize or re-initialize an encrypter
	 * 初始化或重新初始化加密器
	 *
	 * @param EncryptionConfig $config Configuration parameters
	 *
	 * @throws EncryptionException
	 *
	 * @return EncrypterInterface
	 */
	public function initialize(EncryptionConfig $config = null)
	{
		// override config?
		if ($config)
		{
			$this->key    = $config->key;
			$this->driver = $config->driver;
			$this->digest = $config->digest ?? 'SHA512';
		}

		// Insist on a driver
		if (empty($this->driver))
		{
			throw EncryptionException::forNoDriverRequested();
		}

		// Check for an unknown driver
		if (! in_array($this->driver, $this->drivers, true))
		{
			throw EncryptionException::forUnKnownHandler($this->driver);
		}

		if (empty($this->key))
		{
			throw EncryptionException::forNeedsStarterKey();
		}

		// Derive a secret key for the encrypter
		$this->hmacKey = bin2hex(\hash_hkdf($this->digest, $this->key));

		$handlerName     = 'CodeIgniter\\Encryption\\Handlers\\' . $this->driver . 'Handler';
		$this->encrypter = new $handlerName($config);

		return $this->encrypter;
	}

	/**
	 * Create a random key
	 * 创建一个随机密钥
	 *
	 * @param integer $length Output length
	 *
	 * @return string
	 */
	public static function createKey($length = 32)
	{
		return random_bytes($length);
	}

	/**
	 * __get() magic, providing readonly access to some of our protected properties
	 *
	 * @param string $key Property name
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($this->__isset($key))
		{
			return $this->{$key};
		}

		return null;
	}

	/**
	 * __isset() magic, providing checking for some of our protected properties
	 * __isset()magic,为我们的一些保护属性提供检查。
	 *
	 * @param  string $key Property name
	 * @return boolean
	 */
	public function __isset($key): bool
	{
		return in_array($key, ['key', 'digest', 'driver', 'drivers'], true);
	}
}
