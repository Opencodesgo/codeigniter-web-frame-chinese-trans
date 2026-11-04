<?php
/**
 * CodeIgniter，调试，工具栏，收集器，定时器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use Config\Services;

/**
 * Timers collector
 * 定时器收集器。
 */
class Timers extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 * 该收藏者是否有可显示在时间线中的数据
	 *
	 * @var boolean
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 * 该收藏者是否需要以标签页形式显示内容
	 *
	 * @var boolean
	 */
	protected $hasTabContent = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 * 此收藏夹的“标题”。用于在工具栏 HTML 中为项目命名。
	 *
	 * @var string
	 */
	protected $title = 'Timers';

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 * 子类应实现此功能，以返回格式化后的时间线数据，便于正确使用。
	 *
	 * @return array
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		$benchmark = Services::timer(true);
		$rows      = $benchmark->getTimers(6);

		foreach ($rows as $name => $info)
		{
			if ($name === 'total_execution')
			{
				continue;
			}

			$data[] = [
				'name'      => ucwords(str_replace('_', ' ', $name)),
				'component' => 'Timer',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start'],
			];
		}

		return $data;
	}
}
