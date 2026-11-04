<?php
/**
 * CodeIgniter，节流阀，Throttler
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Throttle;

use CodeIgniter\Cache\CacheInterface;

/**
 * Class Throttler
 * 节流阀类。
 *
 * Uses an implementation of the Token Bucket algorithm to implement a
 * "rolling window" type of throttling that can be used for rate limiting
 * an API or any other request.
 * 采用令牌桶算法的实现，用于实现一种“滑动窗口”类型的限流机制，可用于限制API或其他请求的速率。
 *
 * Each "token" in the "bucket" is equivalent to a single request
 * for the purposes of this implementation.
 *
 * @see https://en.wikipedia.org/wiki/Token_bucket
 */
class Throttler implements ThrottlerInterface
{
	/**
	 * Container for throttle counters.
	 * 节流阀计数器容器
	 *
	 * @var \CodeIgniter\Cache\CacheInterface
	 */
	protected $cache;

	/**
	 * The number of seconds until the next token is available.
	 * 到下一个令牌可用的秒数
	 *
	 * @var integer
	 */
	protected $tokenTime = 0;

	/**
	 * The prefix applied to all keys to
	 * minimize potential conflicts.
	 * 用于所有键的前缀，以尽量减少潜在冲突。
	 *
	 * @var string
	 */
	protected $prefix = 'throttler_';

	/**
	 * Timestamp to use (during testing)
	 * 要使用的时间戳（在测试期间）
	 *
	 * @var integer
	 */
	protected $testTime;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param CacheInterface $cache
	 */
	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of seconds until the next available token will
	 * be released for usage.
	 * 返回下次可用令牌释放前的秒数
	 *
	 * @return integer
	 */
	public function getTokenTime(): int
	{
		return $this->tokenTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Restricts the number of requests made by a single IP address within
	 * a set number of seconds.
	 * 限制单个IP地址在指定时间内发出的请求数量。
	 *
	 * Example:
	 *
	 *  if (! $throttler->check($request->ipAddress(), 60, MINUTE))
	 * {
	 *      die('You submitted over 60 requests within a minute.');
	 * }
	 *
	 * @param string  $key      The name to use as the "bucket" name.
	 * @param integer $capacity The number of requests the "bucket" can hold
	 * @param integer $seconds  The time it takes the "bucket" to completely refill
	 * @param integer $cost     The number of tokens this action uses.
	 *
	 * @return   boolean
	 * @internal param int $maxRequests
	 */
	public function check(string $key, int $capacity, int $seconds, int $cost = 1): bool
	{
		$tokenName = $this->prefix . $key;

		// Check to see if the bucket has even been created yet.
		// 检查桶是否已经创建
		if (($tokens = $this->cache->get($tokenName)) === null)
		{
			// If it hasn't been created, then we'll set it to the maximum
			// capacity - 1, and save it to the cache.
			$this->cache->save($tokenName, $capacity - $cost, $seconds);
			$this->cache->save($tokenName . 'Time', time(), $seconds);

			return true;
		}

		// If $tokens > 0, then we need to replenish the bucket
		// based on how long it's been since the last update.
		$throttleTime = $this->cache->get($tokenName . 'Time');
		$elapsed      = $this->time() - $throttleTime;

		// Number of tokens to add back per second
		$rate = $capacity / $seconds;

		// How many seconds till a new token is available.
		// We must have a minimum wait of 1 second for a new token.
		// Primarily stored to allow devs to report back to users.
		$newTokenAvailable = (1 / $rate) - $elapsed;
		$this->tokenTime   = max(1, $newTokenAvailable);

		// Add tokens based up on number per second that
		// should be refilled, then checked against capacity
		// to be sure the bucket didn't overflow.
		$tokens += $rate * $elapsed;
		$tokens = $tokens > $capacity ? $capacity : $tokens;

		// If $tokens >= 1, then we are safe to perform the action, but
		// we need to decrement the number of available tokens.
		if ($tokens >= 1)
		{
			$this->cache->save($tokenName, $tokens - $cost, $seconds);
			$this->cache->save($tokenName . 'Time', time(), $seconds);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Used during testing to set the current timestamp to use.
	 * 在测试期间使用，用于设置要使用的当前时间戳。
	 *
	 * @param integer $time
	 *
	 * @return $this
	 */
	public function setTestTime(int $time)
	{
		$this->testTime = $time;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Return the test time, defaulting to current.
	 * 返回测试时间，默认为current。
	 *
	 * @return integer
	 */
	public function time(): int
	{
		return $this->testTime ?? time();
	}
}
