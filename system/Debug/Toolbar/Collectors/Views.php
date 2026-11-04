<?php
/**
 * CodeIgniter，调试，工具栏，收集器，视图
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

use CodeIgniter\View\RendererInterface;
use Config\Services;

/**
 * Views collector
 * 视图收集器。
 */
class Views extends BaseCollector
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
	 * Whether this collector needs to display
	 * a label or not.
	 * 该收藏者是否需要显示标签
	 *
	 * @var boolean
	 */
	protected $hasLabel = true;

	/**
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 * 该收藏者是否有数据应显示在“变量”选项卡中
	 *
	 * @var boolean
	 */
	protected $hasVarData = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Views';

	/**
	 * Instance of the Renderer service
	 * Renderer服务的实例
	 *
	 * @var RendererInterface
	 */
	protected $viewer;

	/**
	 * Views counter
	 * 视图计数器
	 *
	 * @var array
	 */
	protected $views = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->viewer = Services::renderer();
	}

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

		$rows = $this->viewer->getPerformanceData(); // @phpstan-ignore-line

		foreach ($rows as $info)
		{
			$data[] = [
				'name'      => 'View: ' . $info['view'],
				'component' => 'Views',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start'],
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets a collection of data that should be shown in the 'Vars' tab.
	 * The format is an array of sections, each with their own array
	 * of key/value pairs:
	 * 获取一组应在“变量”选项卡中显示的数据。格式为一个包含多个部分的数组，每个部分都包含其自身的键值对数组：
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
	 *
	 * @return array
	 */
	public function getVarData(): array
	{
		return [
			// @phpstan-ignore-next-line
			'View Data' => $this->viewer->getData(),
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a count of all views.
	 * 返回所有视图的计数
	 *
	 * @return integer
	 */
	public function getBadgeValue(): int
	{
		return count($this->viewer->getPerformanceData()); // @phpstan-ignore-line
	}

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
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADeSURBVEhL7ZSxDcIwEEWNYA0YgGmgyAaJLTcUaaBzQQEVjMEabBQxAdw53zTHiThEovGTfnE/9rsoRUxhKLOmaa6Uh7X2+UvguLCzVxN1XW9x4EYHzik033Hp3X0LO+DaQG8MDQcuq6qao4qkHuMgQggLvkPLjqh00ZgFDBacMJYFkuwFlH1mshdkZ5JPJERA9JpI6xNCBESvibQ+IURA9JpI6xNCBESvibQ+IURA9DTsuHTOrVFFxixgB/eUFlU8uKJ0eDBFOu/9EvoeKnlJS2/08Tc8NOwQ8sIfMeYFjqKDjdU2sp4AAAAASUVORK5CYII=';
	}
}
