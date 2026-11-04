<?php
/**
 * CodeIgniter，缓存，缓存接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache;

/**
 * Cache interface
 * 缓存接口
 */
interface CacheInterface
{
	/**
	 * Takes care of any handler-specific setup that must be done.
	 * 负责任何必须完成的特定于处理程序的设置
	 */
	public function initialize();

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 * 尝试从缓存存储区获取项
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function get(string $key);

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
	public function save(string $key, $value, int $ttl = 60);

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 * 从高速缓存存储区删除特定项
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function delete(string $key);

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
	public function increment(string $key, int $offset = 1);

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
	public function decrement(string $key, int $offset = 1);

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 * 将删除整个缓存中的所有项
	 *
	 * @return mixed
	 */
	public function clean();

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
	public function getCacheInfo();

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 * 返回关于缓存中特定项的详细信息
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData(string $key);

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 * 确定此系统是否支持该驱动程序
	 *
	 * @return boolean
	 */
	public function isSupported(): bool;

	//--------------------------------------------------------------------
}
