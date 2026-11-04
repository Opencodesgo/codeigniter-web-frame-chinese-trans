<?php
/**
 * 配置，缓存
 */

namespace Config;

use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Primary Handler	主处理程序
	 * --------------------------------------------------------------------------
	 *
	 * The name of the preferred handler that should be used. If for some reason
	 * it is not available, the $backupHandler will be used in its place.
	 * 应使用的首选处理器名称。如果因某些原因不可用，则将使用 $backupHandler 作为替代。
	 *
	 * @var string
	 */
	public $handler = 'file';

	/**
	 * --------------------------------------------------------------------------
	 * Backup Handler	备份数据处理程序
	 * --------------------------------------------------------------------------
	 *
	 * The name of the handler that will be used in case the first one is
	 * unreachable. Often, 'file' is used here since the filesystem is
	 * always available, though that's not always practical for the app.
	 * 如果第一个处理程序不可用，则将使用该处理程序的名称。
	 * 通常在这里使用“文件”，因为文件系统始终可用，尽管这对应用程序来说并不总是可行的。
	 *
	 * @var string
	 */
	public $backupHandler = 'dummy';

	/**
	 * --------------------------------------------------------------------------
	 * Cache Directory Path	缓存目录路径
	 * --------------------------------------------------------------------------
	 *
	 * The path to where cache files should be stored, if using a file-based
	 * system.
	 * 如果使用基于文件的系统，缓存文件应存储的路径。
	 *
	 * @var string
	 *
	 * @deprecated Use the driver-specific variant under $file
	 */
	public $storePath = WRITEPATH . 'cache/';

	/**
	 * --------------------------------------------------------------------------
	 * Cache Include Query String	缓存包含查询字符串
	 * --------------------------------------------------------------------------
	 *
	 * Whether to take the URL query string into consideration when generating
	 * output cache files. Valid options are:
	 * 在生成输出缓存文件时，是否考虑URL查询字符串。有效选项包括：
	 *
	 *    false      = Disabled
	 *    true       = Enabled, take all query parameters into account.
	 *                 Please be aware that this may result in numerous cache
	 *                 files generated for the same page over and over again.
	 *    array('q') = Enabled, but only take into account the specified list
	 *                 of query parameters.
	 *
	 * @var boolean|string[]
	 */
	public $cacheQueryString = false;

	/**
	 * --------------------------------------------------------------------------
	 * Key Prefix	键前缀
	 * --------------------------------------------------------------------------
	 *
	 * This string is added to all cache item names to help avoid collisions
	 * if you run multiple applications with the same cache engine.
	 * 此字符串会添加到所有缓存项名称中，以帮助在使用相同缓存引擎运行多个应用程序时避免冲突。
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * --------------------------------------------------------------------------
	 * File settings	文件设置
	 * --------------------------------------------------------------------------
	 * Your file storage preferences can be specified below, if you are using
	 * the File driver.
	 * 如果您使用的是文件驱动器，可以在下方指定您的文件存储偏好。
	 *
	 * @var array<string, string|int|null>
	 */
	public $file = [
		'storePath' => WRITEPATH . 'cache/',
		'mode'      => 0640,
	];

	/**
	 * -------------------------------------------------------------------------
	 * Memcached settings	Memcached的设置
	 * -------------------------------------------------------------------------
	 * Your Memcached servers can be specified below, if you are using
	 * the Memcached drivers.
	 * 如果您使用了 Memcached 驱动程序，可以在下方指定您的 Memcached 服务器。
	 *
	 * @see https://codeigniter.com/user_guide/libraries/caching.html#memcached
	 *
	 * @var array<string, string|int|boolean>
	 */
	public $memcached = [
		'host'   => '127.0.0.1',
		'port'   => 11211,
		'weight' => 1,
		'raw'    => false,
	];

	/**
	 * -------------------------------------------------------------------------
	 * Redis settings	Redis 设置
	 * -------------------------------------------------------------------------
	 * Your Redis server can be specified below, if you are using
	 * the Redis or Predis drivers.
	 * 如果您使用 Redis 或 Predis 驱动程序，可以在下方指定您的 Redis 服务器。
	 *
	 * @var array<string, string|int|null>
	 */
	public $redis = [
		'host'     => '127.0.0.1',
		'password' => null,
		'port'     => 6379,
		'timeout'  => 0,
		'database' => 0,
	];

	/**
	 * --------------------------------------------------------------------------
	 * Available Cache Handlers	可用的缓存处理程序
	 * --------------------------------------------------------------------------
	 *
	 * This is an array of cache engine alias' and class names. Only engines
	 * that are listed here are allowed to be used.
	 * 这是一个缓存引擎别名和类名的数组。仅允许使用此处列出的引擎。
	 *
	 * @var array<string, string>
	 */
	public $validHandlers = [
		'dummy'     => DummyHandler::class,
		'file'      => FileHandler::class,
		'memcached' => MemcachedHandler::class,
		'predis'    => PredisHandler::class,
		'redis'     => RedisHandler::class,
		'wincache'  => WincacheHandler::class,
	];
}
