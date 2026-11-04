<?php
/**
 * CodeIgniter，测试，模拟，模拟事件
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

use CodeIgniter\Events\Events;

/**
 * Events
 * 事件。
 */
class MockEvents extends Events
{
	public function getListeners()
	{
		return self::$listeners;
	}

	public function getEventsFile()
	{
		return self::$files;
	}

	public function getSimulate()
	{
		return self::$simulate;
	}

	public function unInitialize()
	{
		static::$initialized = false;
	}
}
