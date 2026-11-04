<?php
/**
 * CodeIgniter，缓存，处理程序，Wincache 处理程序
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

use Config\Cache;

/**
 * Cache handler for WinCache from Microsoft & IIS.
 * Windows-only, so not testable on travis-ci.
 * Unusable methods flagged for code coverage ignoring.
 * 缓存处理程序的WinCache从微软和IIS。仅限windows，因此不能在travis-ci上测试。
 */
class WincacheHandler extends BaseHandler
{
	/**
	 * Prefixed to all cache names.
	 * 所有缓存名称的前缀
	 *
	 * @var string
	 */
	protected $prefix;

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
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 * 负责任何必须完成的特定于处理程序的设置
	 *
	 * @codeCoverageIgnore
	 */
	public function initialize()
	{
		// Nothing to see here...
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 * 尝试从缓存存储区获取项
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore
	 */
	public function get(string $key)
	{
		$key = $this->prefix . $key;

		$success = false;
		$data    = wincache_ucache_get($key, $success);

		// Success returned by reference from wincache_ucache_get()
		return ($success) ? $data : null;
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
	 *
	 * @codeCoverageIgnore
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = $this->prefix . $key;

		return wincache_ucache_set($key, $value, $ttl);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 * 从高速缓存存储区删除特定项
	 *
	 * @param string $key Cache item name
	 *
	 * @return boolean
	 *
	 * @codeCoverageIgnore
	 */
	public function delete(string $key)
	{
		$key = $this->prefix . $key;

		return wincache_ucache_delete($key);
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
	 *
	 * @codeCoverageIgnore
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$success = false;
		$value   = wincache_ucache_inc($key, $offset, $success);

		return ($success === true) ? $value : false; // @phpstan-ignore-line
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
	 *
	 * @codeCoverageIgnore
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$success = false;
		$value   = wincache_ucache_dec($key, $offset, $success);

		return ($success === true) ? $value : false; // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 * 将删除整个缓存中的所有项
	 *
	 * @return boolean
	 *
	 * @codeCoverageIgnore
	 */
	public function clean()
	{
		return wincache_ucache_clear();
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
	 *
	 * @codeCoverageIgnore
	 */
	public function getCacheInfo()
	{
		return wincache_ucache_info(true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 * 返回关于缓存中特定项的详细信息
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore
	 */
	public function getMetaData(string $key)
	{
		$key = $this->prefix . $key;

		if ($stored = wincache_ucache_info(false, $key))
		{
			$age      = $stored['ucache_entries'][1]['age_seconds'];
			$ttl      = $stored['ucache_entries'][1]['ttl_seconds'];
			$hitcount = $stored['ucache_entries'][1]['hitcount'];

			return [
				'expire'   => $ttl - $age,
				'hitcount' => $hitcount,
				'age'      => $age,
				'ttl'      => $ttl,
			];
		}

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
		return (extension_loaded('wincache') && ini_get('wincache.ucenabled'));
	}

	//--------------------------------------------------------------------
}
