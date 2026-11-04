<?php
/**
 * CodeIgniter，配置，视图
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

/**
 * View configuration
 * 视图配置。
 */
class View extends BaseConfig
{
	/**
	 * When false, the view method will clear the data between each
	 * call.
	 * 当为假时，视图方法会在每次调用之间清除数据。
	 *
	 * @var boolean
	 */
	public $saveData = true;

	/**
	 * Parser Filters map a filter name with any PHP callable. When the
	 * Parser prepares a variable for display, it will chain it
	 * through the filters in the order defined, inserting any parameters.
	 * 解析器过滤器将过滤器名称映射为任何PHP可调用对象。
	 * 当解析器准备显示变量时，会按照定义的顺序通过过滤器链，并插入任何参数。
	 *
	 * To prevent potential abuse, all filters MUST be defined here
	 * in order for them to be available for use within the Parser.
	 * 为防止潜在滥用，所有过滤器都必须在此处定义，以便在解析器中可用。
	 */
	public $filters = [];

	/**
	 * Parser Plugins provide a way to extend the functionality provided
	 * by the core Parser by creating aliases that will be replaced with
	 * any callable. Can be single or tag pair.
	 * 解析器插件提供了一种扩展核心解析器功能的方法，通过创建别名，
	 * 这些别名将被替换为任何可调用函数。可以是单个别名或标签对。
	 */
	public $plugins = [];

	/**
	 * Built-in View filters.
	 * 内置视图过滤器
	 *
	 * @var array
	 */
	protected $coreFilters = [
		'abs'            => '\abs',
		'capitalize'     => '\CodeIgniter\View\Filters::capitalize',
		'date'           => '\CodeIgniter\View\Filters::date',
		'date_modify'    => '\CodeIgniter\View\Filters::date_modify',
		'default'        => '\CodeIgniter\View\Filters::default',
		'esc'            => '\CodeIgniter\View\Filters::esc',
		'excerpt'        => '\CodeIgniter\View\Filters::excerpt',
		'highlight'      => '\CodeIgniter\View\Filters::highlight',
		'highlight_code' => '\CodeIgniter\View\Filters::highlight_code',
		'limit_words'    => '\CodeIgniter\View\Filters::limit_words',
		'limit_chars'    => '\CodeIgniter\View\Filters::limit_chars',
		'local_currency' => '\CodeIgniter\View\Filters::local_currency',
		'local_number'   => '\CodeIgniter\View\Filters::local_number',
		'lower'          => '\strtolower',
		'nl2br'          => '\CodeIgniter\View\Filters::nl2br',
		'number_format'  => '\number_format',
		'prose'          => '\CodeIgniter\View\Filters::prose',
		'round'          => '\CodeIgniter\View\Filters::round',
		'strip_tags'     => '\strip_tags',
		'title'          => '\CodeIgniter\View\Filters::title',
		'upper'          => '\strtoupper',
	];

	/**
	 * Built-in View plugins.
	 * 内置视图插件
	 *
	 * @var array
	 */
	protected $corePlugins = [
		'current_url'       => '\CodeIgniter\View\Plugins::currentURL',
		'previous_url'      => '\CodeIgniter\View\Plugins::previousURL',
		'mailto'            => '\CodeIgniter\View\Plugins::mailto',
		'safe_mailto'       => '\CodeIgniter\View\Plugins::safeMailto',
		'lang'              => '\CodeIgniter\View\Plugins::lang',
		'validation_errors' => '\CodeIgniter\View\Plugins::validationErrors',
		'route'             => '\CodeIgniter\View\Plugins::route',
		'siteURL'           => '\CodeIgniter\View\Plugins::siteURL',
	];

	/**
	 * Constructor.
	 * 构造函数。
	 *
	 * Merge the built-in and developer-configured filters and plugins,
	 * with preference to the developer ones.
	 * 将内置和开发器配置的过滤器和插件合并,更倾向于开发人员。
	 */
	public function __construct()
	{
		$this->filters = array_merge($this->coreFilters, $this->filters);
		$this->plugins = array_merge($this->corePlugins, $this->plugins);

		parent::__construct();
	}
}
