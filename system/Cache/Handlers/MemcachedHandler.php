<?php
/**
 * CodeIgniter，缓存，处理程序，Memcached 处理程序
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Exceptions\CriticalError;
use Config\Cache;
use Exception;
use Memcache;
use Memcached;

/**
 * Mamcached cache handler
 * Memcached 缓存处理程序。
 */
class MemcachedHandler extends BaseHandler
{
	/**
	 * Prefixed to all cache names.
	 * 所有缓存名称的前缀
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * The memcached object
	 * memcached对象
	 *
	 * @var Memcached|Memcache
	 */
	protected $memcached;

	/**
	 * Memcached Configuration
	 * Memcached配置
	 *
	 * @var array
	 */
	protected $config = [
		'host'   => '127.0.0.1',
		'port'   => 11211,
		'weight' => 1,
		'raw'    => false,
	];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param Cache $config
	 */
	public function __construct(Cache $config)
	{
		$this->prefix = (string) $config->prefix;

		if (! empty($config))
		{
			$this->config = array_merge($this->config, $config->memcached);
		}
	}

	/**
	 * Class destructor
	 * 类的析构函数。
	 *
	 * Closes the connection to Memcache(d) if present.
	 * 关闭与Memcache(d)的连接（如果存在）。
	 */
	public function __destruct()
	{
		if ($this->memcached instanceof Memcached)
		{
			$this->memcached->quit();
		}
		elseif ($this->memcached instanceof Memcache)
		{
			$this->memcached->close();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 * 负责任何必须完成的特定于处理程序的设置
	 */
	public function initialize()
	{
		// Try to connect to Memcache or Memcached, if an issue occurs throw a CriticalError exception,
		// so that the CacheFactory can attempt to initiate the next cache handler.
		// 尝试连接到 Memcache 或 Memcached，如果出现问题，请抛出 CriticalError 异常，以便 CacheFactory 可以尝试启动下一个缓存处理器。
		try
		{
			if (class_exists(Memcached::class))
			{
				// Create new instance of Memcached
				$this->memcached = new Memcached();
				if ($this->config['raw'])
				{
					$this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
				}

				// Add server
				$this->memcached->addServer(
					$this->config['host'], $this->config['port'], $this->config['weight']
				);

				// attempt to get status of servers
				$stats = $this->memcached->getStats();

				// $stats should be an associate array with a key in the format of host:port.
				// If it doesn't have the key, we know the server is not working as expected.
				if (! isset($stats[$this->config['host'] . ':' . $this->config['port']]))
				{
					throw new CriticalError('Cache: Memcached connection failed.');
				}
			}
			elseif (class_exists(Memcache::class))
			{
				// Create new instance of Memcache
				$this->memcached = new Memcache();

				// Check if we can connect to the server
				$canConnect = $this->memcached->connect(
					$this->config['host'], $this->config['port']
				);

				// If we can't connect, throw a CriticalError exception
				if ($canConnect === false)
				{
					throw new CriticalError('Cache: Memcache connection failed.');
				}

				// Add server, third parameter is persistence and defaults to TRUE.
				$this->memcached->addServer(
					$this->config['host'], $this->config['port'], true, $this->config['weight']
				);
			}
			else
			{
				throw new CriticalError('Cache: Not support Memcache(d) extension.');
			}
		}
		catch (CriticalError $e)
		{
			// If a CriticalError exception occurs, throw it up.
			throw $e;
		}
		catch (Exception $e)
		{
			// If an \Exception occurs, convert it into a CriticalError exception and throw it.
			throw new CriticalError('Cache: Memcache(d) connection refused (' . $e->getMessage() . ').');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 * 尝试从缓存存储区获取项
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function get(string $key)
	{
		$key = $this->prefix . $key;

		if ($this->memcached instanceof Memcached)
		{
			$data = $this->memcached->get($key);

			// check for unmatched key
			if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND)
			{
				return null;
			}
		}
		elseif ($this->memcached instanceof Memcache)
		{
			$flags = false;
			$data  = $this->memcached->get($key, $flags); // @phpstan-ignore-line

			// check for unmatched key (i.e. $flags is untouched)
			if ($flags === false)
			{
				return null;
			}
		}

		return is_array($data) ? $data[0] : $data; // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 * 将项保存到缓存存储区
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value The data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = $this->prefix . $key;

		if (! $this->config['raw'])
		{
			$value = [
				$value,
				time(),
				$ttl,
			];
		}

		if ($this->memcached instanceof Memcached)
		{
			return $this->memcached->set($key, $value, $ttl);
		}

		if ($this->memcached instanceof Memcache)
		{
			return $this->memcached->set($key, $value, 0, $ttl);
		}

		// @phpstan-ignore-next-line
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 * 从高速缓存存储区删除特定项
	 *
	 * @param string $key Cache item name
	 *
	 * @return boolean
	 */
	public function delete(string $key)
	{
		$key = $this->prefix . $key;

		return $this->memcached->delete($key);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 * 对原始存储值执行原子递增
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1)
	{
		if (! $this->config['raw'])
		{
			return false;
		}

		$key = $this->prefix . $key;

		// @phpstan-ignore-next-line
		return $this->memcached->increment($key, $offset, $offset, 60);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 * 对原始存储值执行原子递减
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1)
	{
		if (! $this->config['raw'])
		{
			return false;
		}

		$key = $this->prefix . $key;

		//FIXME: third parameter isn't other handler actions.
		// @phpstan-ignore-next-line
		return $this->memcached->decrement($key, $offset, $offset, 60);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 * 将删除整个缓存中的所有项
	 *
	 * @return boolean
	 */
	public function clean()
	{
		return $this->memcached->flush();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 * 返回关于整个缓存的信息。
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 * 返回的信息和数据结构因处理程序而异。
	 *
	 * @return mixed
	 */
	public function getCacheInfo()
	{
		return $this->memcached->getStats();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 * 返回关于缓存中特定项的详细信息
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData(string $key)
	{
		$key = $this->prefix . $key;

		$stored = $this->memcached->get($key);

		// if not an array, don't try to count for PHP7.2
		if (! is_array($stored) || count($stored) !== 3)
		{
			return false;
		}

		list($data, $time, $ttl) = $stored;

		return [
			'expire' => $time + $ttl,
			'mtime'  => $time,
			'data'   => $data,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 * 确定此系统是否支持该驱动程序
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return (extension_loaded('memcached') || extension_loaded('memcache'));
	}
}
