<?php
/**
 * CodeIgniter，缓存，处理程序，Predis 处理程序
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
use Predis\Client;

/**
 * Predis cache handler
 * Predis 缓存处理程序。
 */
class PredisHandler extends BaseHandler
{
	/**
	 * Prefixed to all cache names.
	 * 所有缓存名称的前缀
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Default config
	 * 默认配置
	 *
	 * @var array
	 */
	protected $config = [
		'scheme'   => 'tcp',
		'host'     => '127.0.0.1',
		'password' => null,
		'port'     => 6379,
		'timeout'  => 0,
	];

	/**
	 * Predis connection
	 * Predis 连接
	 *
	 * @var Client
	 */
	protected $redis;

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

		if (isset($config->redis))
		{
			$this->config = array_merge($this->config, $config->redis);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 * 负责任何必须完成的特定于处理程序的设置
	 */
	public function initialize()
	{
		// Try to connect to Redis, if an issue occurs throw a CriticalError exception,
		// so that the CacheFactory can attempt to initiate the next cache handler.
		try
		{
			// Create a new instance of Predis\Client
			$this->redis = new Client($this->config, ['prefix' => $this->prefix]);

			// Check if the connection is valid by trying to get the time.
			$this->redis->time();
		}
		catch (Exception $e)
		{
			// thrown if can't connect to redis server.
			throw new CriticalError('Cache: Predis connection refused (' . $e->getMessage() . ').');
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
		$data = array_combine([
			'__ci_type',
			'__ci_value',
		], $this->redis->hmget($key, ['__ci_type', '__ci_value'])
		);

		if (! isset($data['__ci_type'], $data['__ci_value']) || $data['__ci_value'] === false)
		{
			return null;
		}

		switch ($data['__ci_type'])
		{
			case 'array':
			case 'object':
				return unserialize($data['__ci_value']);
			case 'boolean':
			case 'integer':
			case 'double': // Yes, 'double' is returned and NOT 'float'
			case 'string':
			case 'NULL':
				return settype($data['__ci_value'], $data['__ci_type']) ? $data['__ci_value'] : null;
			case 'resource':
			default:
				return null;
		}
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
		switch ($dataType = gettype($value))
		{
			case 'array':
			case 'object':
				$value = serialize($value);
				break;
			case 'boolean':
			case 'integer':
			case 'double': // Yes, 'double' is returned and NOT 'float'
			case 'string':
			case 'NULL':
				break;
			case 'resource':
			default:
				return false;
		}

		if (! $this->redis->hmset($key, ['__ci_type' => $dataType, '__ci_value' => $value]))
		{
			return false;
		}

		$this->redis->expireat($key, time() + $ttl);

		return true;
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
		return ($this->redis->del($key) === 1);
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
		return $this->redis->hincrby($key, 'data', $offset);
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
		return $this->redis->hincrby($key, 'data', -$offset);
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
		return $this->redis->flushdb()->getPayload() === 'OK';
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
		return $this->redis->info();
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
		$data = array_combine(['__ci_value'], $this->redis->hmget($key, ['__ci_value']));

		if (isset($data['__ci_value']) && $data['__ci_value'] !== false)
		{
			$time = time();
			return [
				'expire' => $time + $this->redis->ttl($key),
				'mtime'  => $time,
				'data'   => $data['__ci_value'],
			];
		}

		return null;
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
		return class_exists('\Predis\Client');
	}
}
