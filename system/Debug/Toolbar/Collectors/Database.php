<?php
/**
 * CodeIgniter，调试，工具栏，收集器，数据库
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

use CodeIgniter\Database\Query;

/**
 * Collector for the Database tab of the Debug Toolbar.
 * “调试工具栏”的“数据库”选项卡的收集器
 */
class Database extends BaseCollector
{
	/**
	 * Whether this collector has timeline data.
	 * 此收集器是否具有时间轴数据
	 *
	 * @var boolean
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector should display its own tab.
	 * 此收集器是否应该显示其自己的选项卡
	 *
	 * @var boolean
	 */
	protected $hasTabContent = true;

	/**
	 * Whether this collector has data for the Vars tab.
	 * 此收集器是否有Vars选项卡的数据
	 *
	 * @var boolean
	 */
	protected $hasVarData = false;

	/**
	 * The name used to reference this collector in the toolbar.
	 * 用于在工具栏中引用此收集器的名称
	 *
	 * @var string
	 */
	protected $title = 'Database';

	/**
	 * Array of database connections.
	 * 数据库连接数组
	 *
	 * @var array
	 */
	protected $connections;

	/**
	 * The query instances that have been collected
	 * through the DBQuery Event.
	 * 通过DBQuery事件收集的查询实例
	 *
	 * @var Query[]
	 */
	protected static $queries = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->connections = \Config\Database::getConnections();
	}

	//--------------------------------------------------------------------

	/**
	 * The static method used during Events to collect
	 * data.
	 * 在事件期间用于收集数据的静态方法
	 *
	 * @param Query $query
	 *
	 * @internal param $ array \CodeIgniter\Database\Query
	 */
	public static function collect(Query $query)
	{
		$config = config('Toolbar');

		// Provide default in case it's not set
		$max = $config->maxQueries ?: 100;

		if (count(static::$queries) < $max)
		{
			static::$queries[] = $query;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns timeline data formatted for the toolbar.
	 * 返回为工具栏格式化的时间轴数据
	 *
	 * @return array The formatted data or an empty array.
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		foreach ($this->connections as $alias => $connection)
		{
			// Connection Time
			$data[] = [
				'name'      => 'Connecting to Database: "' . $alias . '"',
				'component' => 'Database',
				'start'     => $connection->getConnectStart(),
				'duration'  => $connection->getConnectDuration(),
			];
		}

		foreach (static::$queries as $query)
		{
			$data[] = [
				'name'      => 'Query',
				'component' => 'Database',
				'start'     => $query->getStartTime(true),
				'duration'  => $query->getDuration(),
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 * 返回要在工具栏中格式化的此收集器的数据
	 *
	 * @return array
	 */
	public function display(): array
	{
		$data['queries'] = array_map(function (Query $query) {
			return [
				'duration' => ((float) $query->getDuration(5) * 1000) . ' ms',
				'sql'      => $query->debugToolbarDisplay(),
			];
		}, static::$queries);

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the "badge" value for the button.
	 * 获取按钮的“badge”值
	 *
	 * @return integer
	 */
	public function getBadgeValue(): int
	{
		return count(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Information to be displayed next to the title.
	 * 要显示在标题旁边的信息
	 *
	 * @return string The number of queries (in parentheses) or an empty string.
	 */
	public function getTitleDetails(): string
	{
		return '(' . count(static::$queries) . ' Queries across ' . ($countConnection = count($this->connections)) . ' Connection' .
				($countConnection > 1 ? 's' : '') . ')';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have any data collected?
	 * 这个收集器是否收集了任何数据？
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool
	{
		return empty(static::$queries);
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
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADMSURBVEhLY6A3YExLSwsA4nIycQDIDIhRWEBqamo/UNF/SjDQjF6ocZgAKPkRiFeEhoYyQ4WIBiA9QAuWAPEHqBAmgLqgHcolGQD1V4DMgHIxwbCxYD+QBqcKINseKo6eWrBioPrtQBq/BcgY5ht0cUIYbBg2AJKkRxCNWkDQgtFUNJwtABr+F6igE8olGQD114HMgHIxAVDyAhA/AlpSA8RYUwoeXAPVex5qHCbIyMgwBCkAuQJIY00huDBUz/mUlBQDqHGjgBjAwAAACexpph6oHSQAAAAASUVORK5CYII=';
	}
}
