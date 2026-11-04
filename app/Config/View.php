<?php
/**
 * 配置，视图
 */

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
	/**
	 * When false, the view method will clear the data between each
	 * call. This keeps your data safe and ensures there is no accidental
	 * leaking between calls, so you would need to explicitly pass the data
	 * to each view. You might prefer to have the data stick around between
	 * calls so that it is available to all views. If that is the case,
	 * set $saveData to true.
	 * 当为假时，视图方法会在每次调用之间清除数据。
	 * 这能确保您的数据安全，并防止通话之间意外泄露，因此您需要明确地将数据传递给每个视图。
	 *
	 * @var boolean
	 */
	public $saveData = true;

	/**
	 * Parser Filters map a filter name with any PHP callable. When the
	 * Parser prepares a variable for display, it will chain it
	 * through the filters in the order defined, inserting any parameters.
	 * To prevent potential abuse, all filters MUST be defined here
	 * in order for them to be available for use within the Parser.
	 * 解析器过滤器将过滤器名称映射为任何PHP可调用对象。
	 * 当解析器准备显示变量时，会按照定义的顺序通过过滤器链，并插入任何参数。
	 *
	 * Examples:
	 *  { title|esc(js) }
	 *  { created_on|date(Y-m-d)|esc(attr) }
	 *
	 * @var array
	 */
	public $filters = [];

	/**
	 * Parser Plugins provide a way to extend the functionality provided
	 * by the core Parser by creating aliases that will be replaced with
	 * any callable. Can be single or tag pair.
	 * 解析器插件提供了一种扩展核心解析器功能的方法，通过创建别名，
	 * 这些别名将被替换为任何可调用函数。可以是单个别名或标签对。
	 *
	 * @var array
	 */
	public $plugins = [];
}
