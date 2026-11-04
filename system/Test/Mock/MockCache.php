<?php
/**
 * CodeIgniter，测试，模拟，模拟缓存
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Cache\CacheInterface;
use Closure;

class MockCache implements CacheInterface
{
	/**
	 * Prefixed to all cache names.
	 * 所有缓存名称的前缀
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Mock cache storage.
	 * 模拟缓存存储
	 *
	 * @var array
	 */
	protected $cache = [];

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 * 负责任何必须完成的特定于处理程序的设置
	 */
	public function initialize()
	{
		// Not to see here...
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

		return array_key_exists($key, $this->cache)
			? $this->cache[$key]
			: null;
	}

	//--------------------------------------------------------------------

	/**
	 * Get an item from the cache, or execute the given Closure and store the result.
	 * 从缓存中获取一个项，或者执行给定的Closure并存储结果。
	 *
	 * @param string  $key      Cache item name
	 * @param integer $ttl      Time to live
	 * @param Closure $callback Callback return value
	 *
	 * @return mixed
	 */
	public function remember(string $key, int $ttl, Closure $callback)
	{
		$value = $this->get($key);

		if (! is_null($value))
		{
			return $value;
		}

		$this->save($key, $value = $callback(), $ttl);

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 * 将项保存到缓存存储区。
	 *
	 * The $raw parameter is only utilized by Mamcache in order to
	 * allow usage of increment() and decrement().
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value the data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 * @param boolean $raw   Whether to store the raw value.
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60, bool $raw = false)
	{
		$key = $this->prefix . $key;

		$this->cache[$key] = $value;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 * 从高速缓存存储区删除特定项
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function delete(string $key)
	{
		unset($this->cache[$key]);
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
		$key = $this->prefix . $key;

		$data = $this->cache[$key] ?: null;

		if (empty($data))
		{
			$data = 0;
		}
		elseif (! is_int($data))
		{
			return false;
		}

		return $this->save($key, $data + $offset);
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
		$key = $this->prefix . $key;

		$data = $this->cache[$key] ?: null;

		if (empty($data))
		{
			$data = 0;
		}
		elseif (! is_int($data))
		{
			return false;
		}

		return $this->save($key, $data - $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 * 将删除整个缓存中的所有项
	 *
	 * @return mixed
	 */
	public function clean()
	{
		$this->cache = [];
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
		return [];
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
		return false;
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
		return true;
	}

	//--------------------------------------------------------------------

}
