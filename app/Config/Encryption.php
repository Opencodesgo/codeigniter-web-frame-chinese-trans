<?php
/**
 * 配置，加密
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Encryption configuration.
 * 加密配置。
 *
 * These are the settings used for encryption, if you don't pass a parameter
 * array to the encrypter for creation/initialization.
 * 这些是用于加密的设置，如果您未向加密器传递参数数组以进行创建/初始化时使用。
 */
class Encryption extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Encryption Key Starter	加密密钥入门
	 * --------------------------------------------------------------------------
	 *
	 * If you use the Encryption class you must set an encryption key (seed).
	 * You need to ensure it is long enough for the cipher and mode you plan to use.
	 * See the user guide for more info.
	 * 如果使用 Encryption 类，您必须设置一个加密密钥（种子）。
	 * 您需要确保密钥长度足够，以支持您计划使用的加密算法和模式。更多详细信息请参阅用户指南。
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * --------------------------------------------------------------------------
	 * Encryption Driver to Use	使用的加密驱动程序
	 * --------------------------------------------------------------------------
	 *
	 * One of the supported encryption drivers.
	 * 支持的加密驱动程序之一。
	 *
	 * Available drivers:
	 * - OpenSSL
	 * - Sodium
	 *
	 * @var string
	 */
	public $driver = 'OpenSSL';

	/**
	 * --------------------------------------------------------------------------
	 * SodiumHandler's Padding Length in Bytes	以字节为单位的SodiumHandler填充长度
	 * --------------------------------------------------------------------------
	 *
	 * This is the number of bytes that will be padded to the plaintext message
	 * before it is encrypted. This value should be greater than zero.
	 * 这是在加密前对明文消息进行填充的字节数。该值应大于零。
	 *
	 * See the user guide for more information on padding.
	 * 请参阅用户指南,了解更多关于填充的信息。
	 *
	 * @var integer
	 */
	public $blockSize = 16;

	/**
	 * --------------------------------------------------------------------------
	 * Encryption digest	加密消化
	 * --------------------------------------------------------------------------
	 *
	 * HMAC digest to use, e.g. 'SHA512' or 'SHA256'. Default value is 'SHA512'.
	 *
	 * @var string
	 */
	public $digest = 'SHA512';
}
