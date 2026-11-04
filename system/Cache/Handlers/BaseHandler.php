<?php
/**
 * CodeIgniter，缓存，处理程序，基本处理器
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

use Closure;
use CodeIgniter\Cache\CacheInterface;

/**
 * Base class for cache handling
 * 缓存处理的基类。
 */
abstract class BaseHandler implements CacheInterface
{
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
}
