<?php
/**
 * CodeIgniter，调试，计时器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use RuntimeException;

/**
 * Class Timer
 * 计时器类。
 *
 * Provides a simple way to measure the amount of time
 * that elapses between two points.
 * 提供了一种简单的方法来测量两个时间点之间所经过的时间。
 */
class Timer
{
	/**
	 * List of all timers.
	 * 所有计时器的列表
	 *
	 * @var array
	 */
	protected $timers = [];

	//--------------------------------------------------------------------

	/**
	 * Starts a timer running.
	 * 启动计时器运行。
	 *
	 * Multiple calls can be made to this method so that several
	 * execution points can be measured.
	 * 可以多次调用此方法，以便测量多个执行点。
	 *
	 * @param string $name The name of this timer.
	 * @param float  $time Allows user to provide time.
	 *
	 * @return Timer
	 */
	public function start(string $name, float $time = null)
	{
		$this->timers[strtolower($name)] = [
			'start' => ! empty($time) ? $time : microtime(true),
			'end'   => null,
		];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Stops a running timer.
	 * 停止正在运行的计时器。
	 *
	 * If the timer is not stopped before the timers() method is called,
	 * it will be automatically stopped at that point.
	 * 如果在调用 timers() 方法之前未停止计时器，它将在该处自动停止。
	 *
	 * @param string $name The name of this timer.
	 *
	 * @return Timer
	 */
	public function stop(string $name)
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			throw new RuntimeException('Cannot stop timer: invalid name given.');
		}

		$this->timers[$name]['end'] = microtime(true);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of a recorded timer.
	 * 返回记录的计时器的持续时间
	 *
	 * @param string  $name     The name of the timer.
	 * @param integer $decimals Number of decimal places.
	 *
	 * @return null|float       Returns null if timer exists by that name.
	 *                          Returns a float representing the number of
	 *                          seconds elapsed while that timer was running.
	 */
	public function getElapsedTime(string $name, int $decimals = 4)
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			return null;
		}

		$timer = $this->timers[$name];

		if (empty($timer['end']))
		{
			$timer['end'] = microtime(true);
		}

		return (float) number_format($timer['end'] - $timer['start'], $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the array of timers, with the duration pre-calculated for you.
	 * 返回计时器数组，并为您预先计算持续时间。
	 *
	 * @param integer $decimals Number of decimal places
	 *
	 * @return array
	 */
	public function getTimers(int $decimals = 4): array
	{
		$timers = $this->timers;

		foreach ($timers as &$timer)
		{
			if (empty($timer['end']))
			{
				$timer['end'] = microtime(true);
			}

			$timer['duration'] = (float) number_format($timer['end'] - $timer['start'], $decimals);
		}

		return $timers;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether or not a timer with the specified name exists.
	 * 检查是否存在具有指定名称的计时器
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function has(string $name): bool
	{
		return array_key_exists(strtolower($name), $this->timers);
	}

	//--------------------------------------------------------------------
}
