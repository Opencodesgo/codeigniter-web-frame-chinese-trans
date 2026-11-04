<?php
/**
 * 配置，工具栏
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\Toolbar\Collectors\Database;
use CodeIgniter\Debug\Toolbar\Collectors\Events;
use CodeIgniter\Debug\Toolbar\Collectors\Files;
use CodeIgniter\Debug\Toolbar\Collectors\Logs;
use CodeIgniter\Debug\Toolbar\Collectors\Routes;
use CodeIgniter\Debug\Toolbar\Collectors\Timers;
use CodeIgniter\Debug\Toolbar\Collectors\Views;

/**
 * --------------------------------------------------------------------------
 * Debug Toolbar	调试工具栏
 * --------------------------------------------------------------------------
 *
 * The Debug Toolbar provides a way to see information about the performance
 * and state of your application during that page display. By default it will
 * NOT be displayed under production environments, and will only display if
 * `CI_DEBUG` is true, since if it's not, there's not much to display anyway.
 * 调试工具栏可让您在页面显示期间查看应用程序的性能和状态信息。
 * 默认情况下，它不会在生产环境中显示，只有当 `CI_DEBUG` 为真时才会显示，因为如果为假，其实也没什么可显示的。
 */
class Toolbar extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Toolbar Collectors	工具栏收集
	 * --------------------------------------------------------------------------
	 *
	 * List of toolbar collectors that will be called when Debug Toolbar
	 * fires up and collects data from.
	 * 当调试工具栏启动并收集数据时，将被调用的工具栏收集器列表。
	 *
	 * @var string[]
	 */
	public $collectors = [
		Timers::class,
		Database::class,
		Logs::class,
		Views::class,
		// \CodeIgniter\Debug\Toolbar\Collectors\Cache::class,
		Files::class,
		Routes::class,
		Events::class,
	];

	/**
	 * --------------------------------------------------------------------------
	 * Max History	最大历史
	 * --------------------------------------------------------------------------
	 *
	 * `$maxHistory` sets a limit on the number of past requests that are stored,
	 * helping to conserve file space used to store them. You can set it to
	 * 0 (zero) to not have any history stored, or -1 for unlimited history.
	 * “maxHistory”为存储的过去请求的数量设置了一个限制,帮助保存用于存储它们的文件空间。
	 * 你可以把它设置为0(0),没有任何历史存储,或者是无限历史的-1。
	 *
	 * @var integer
	 */
	public $maxHistory = 20;

	/**
	 * --------------------------------------------------------------------------
	 * Toolbar Views Path	工具栏视图路径
	 * --------------------------------------------------------------------------
	 *
	 * The full path to the the views that are used by the toolbar.
	 * This MUST have a trailing slash.
	 * 工具栏所使用的视图的完整路径。必须以斜杠结尾。
	 *
	 * @var string
	 */
	public $viewsPath = SYSTEMPATH . 'Debug/Toolbar/Views/';

	/**
	 * --------------------------------------------------------------------------
	 * Max Queries	最大查询
	 * --------------------------------------------------------------------------
	 *
	 * If the Database Collector is enabled, it will log every query that the
	 * the system generates so they can be displayed on the toolbar's timeline
	 * and in the query log. This can lead to memory issues in some instances
	 * with hundreds of queries.
	 * 如果启用了数据库收集器，它将记录系统生成的每个查询，以便在工具栏的时间轴上显示以及在查询日志中查看。
	 * 在某些情况下，当有数百个查询时，这可能会导致内存问题。
	 *
	 * `$maxQueries` defines the maximum amount of queries that will be stored.
	 *
	 * @var integer
	 */
	public $maxQueries = 100;
}
