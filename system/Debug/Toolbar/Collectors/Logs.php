<?php
/**
 * CodeIgniter，调试，工具栏，收集器，日志
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
 * Loags collector
 * 日志收集器。
 */
class Logs extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 * 该收集器是否有可显示在时间线中的数据
	 *
	 * @var boolean
	 */
	protected $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 * 该收集器是否需要以标签页形式显示内容
	 *
	 * @var boolean
	 */
	protected $hasTabContent = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 * 此收藏夹的“标题”。用于在工具栏 HTML 中为项目命名。
	 *
	 * @var string
	 */
	protected $title = 'Logs';

	/**
	 * Our collected data.
	 * 我们收集的数据
	 *
	 * @var array
	 */
	protected $data;

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 * 返回要在工具栏中格式化的此收集器的数据
	 *
	 * @return array
	 */
	public function display(): array
	{
		return [
			'logs' => $this->collectLogs(),
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector actually have any data to display?
	 * 这个收集器实际上有任何数据要显示吗？
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool
	{
		$this->collectLogs();

		return empty($this->data);
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 * 显示 icon
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACYSURBVEhLYxgFJIHU1FSjtLS0i0D8AYj7gEKMEBkqAaAFF4D4ERCvAFrwH4gDoFIMKSkpFkB+OTEYqgUTACXfA/GqjIwMQyD9H2hRHlQKJFcBEiMGQ7VgAqCBvUgK32dmZspCpagGGNPT0/1BLqeF4bQHQJePpiIwhmrBBEADR1MRfgB0+WgqAmOoFkwANHA0FY0CUgEDAwCQ0PUpNB3kqwAAAABJRU5ErkJggg==';
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures the data has been collected.
	 * 确保数据已被收集
	 */
	protected function collectLogs()
	{
		if (! empty($this->data))
		{
			return $this->data;
		}

		return $this->data = Services::logger(true)->logCache ?? [];
	}

	//--------------------------------------------------------------------
}
