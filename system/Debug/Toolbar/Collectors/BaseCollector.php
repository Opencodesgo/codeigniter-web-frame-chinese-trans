<?php
/**
 * CodeIgniter，调试，工具栏，收集器，基本收集器
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

use CodeIgniter\Debug\Exceptions;

/**
 * Base Toolbar collector
 * 基本工具栏收集器。
 */
class BaseCollector
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
	protected $hasTabContent = false;

	/**
	 * Whether this collector needs to display
	 * a label or not.
	 * 该收集器是否需要显示标签
	 *
	 * @var boolean
	 */
	protected $hasLabel = false;

	/**
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 * 该收集器是否有数据应显示在“变量”选项卡中
	 *
	 * @var boolean
	 */
	protected $hasVarData = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 * 此收藏夹的“标题”。用于在工具栏 HTML 中为项目命名。
	 *
	 * @var string
	 */
	protected $title = '';

	//--------------------------------------------------------------------

	/**
	 * Gets the Collector's title.
	 * 获得收集器的头衔
	 *
	 * @param  boolean $safe
	 * @return string
	 */
	public function getTitle(bool $safe = false): string
	{
		if ($safe)
		{
			return str_replace(' ', '-', strtolower($this->title));
		}

		return $this->title;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns any information that should be shown next to the title.
	 * 返回应该显示在标题旁边的任何信息
	 *
	 * @return string
	 */
	public function getTitleDetails(): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector need it's own tab?
	 * 这个收集器需要它自己的标签吗？
	 *
	 * @return boolean
	 */
	public function hasTabContent(): bool
	{
		return (bool) $this->hasTabContent;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have a label?
	 * 这个收集器有标签吗？
	 *
	 * @return boolean
	 */
	public function hasLabel(): bool
	{
		return (bool) $this->hasLabel;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have information for the timeline?
	 * 这个收集器有时间轴的信息吗？
	 *
	 * @return boolean
	 */
	public function hasTimelineData(): bool
	{
		return (bool) $this->hasTimeline;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the data for the timeline, properly formatted,
	 * or returns an empty array.
	 * 获取时间轴的数据，格式正确，或返回空数组。
	 *
	 * @return array
	 */
	public function timelineData(): array
	{
		if (! $this->hasTimeline)
		{
			return [];
		}

		return $this->formatTimelineData();
	}

	//--------------------------------------------------------------------

	/**
	 * Does this Collector have data that should be shown in the
	 * 'Vars' tab?
	 * 这个收集器是否有数据需要显示在“变量”选项卡中？
	 *
	 * @return boolean
	 */
	public function hasVarData(): bool
	{
		return (bool) $this->hasVarData;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets a collection of data that should be shown in the 'Vars' tab.
	 * The format is an array of sections, each with their own array
	 * of key/value pairs:
	 * 获取一组应在“变量”选项卡中显示的数据。
	 * 格式为一个包含多个部分的数组，每个部分都包含其自身的键值对数组：
	 *
	 *  $data = [
	 *      'section 1' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *      'section 2' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *  ];
	 */
	public function getVarData()
	{
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 * 子类应实现此功能，以返回格式化后的时间线数据，便于正确使用。
	 *
	 * Timeline data should be formatted into arrays that look like:
	 *
	 *  [
	 *      'name'      => 'Database::Query',
	 *      'component' => 'Database',
	 *      'start'     => 10       // milliseconds
	 *      'duration'  => 15       // milliseconds
	 *  ]
	 *
	 * @return array
	 */
	protected function formatTimelineData(): array
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 * 返回要在工具栏中格式化的此收集器的数据
	 *
	 * @return array|string
	 */
	public function display()
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 * 清洁路径。
	 *
	 * This makes nicer looking paths for the error output.
	 * 这为错误输出提供了更好的查找路径。
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function cleanPath(string $file): string
	{
		return Exceptions::cleanPath($file);
	}

	/**
	 * Gets the "badge" value for the button.
	 */
	public function getBadgeValue()
	{
		return null;
	}

	/**
	 * Does this collector have any data collected?
	 * 这个收集器是否收集了任何数据？
	 *
	 * If not, then the toolbar button won't get shown.
	 * 否则，工具栏按钮将不会显示。
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool
	{
		return false;
	}

	/**
	 * Returns the HTML to display the icon. Should either
	 * be SVG, or a base-64 encoded.
	 * 返回用于显示图标的 HTML。应为 SVG 格式，或经过 base64 编码的字符串。
	 *
	 * Recommended dimensions are 24px x 24px
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return '';
	}

	/**
	 * Return settings as an array.
	 * 以数组形式返回设置
	 *
	 * @return array
	 */
	public function getAsArray(): array
	{
		return [
			'title'           => $this->getTitle(),
			'titleSafe'       => $this->getTitle(true),
			'titleDetails'    => $this->getTitleDetails(),
			'display'         => $this->display(),
			'badgeValue'      => $this->getBadgeValue(),
			'isEmpty'         => $this->isEmpty(),
			'hasTabContent'   => $this->hasTabContent(),
			'hasLabel'        => $this->hasLabel(),
			'icon'            => $this->icon(),
			'hasTimelineData' => $this->hasTimelineData(),
			'timelineData'    => $this->timelineData(),
		];
	}
}
