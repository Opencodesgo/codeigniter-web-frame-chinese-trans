<?php
/**
 * 配置，分页
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Templates	模板
	 * --------------------------------------------------------------------------
	 *
	 * Pagination links are rendered out using views to configure their
	 * appearance. This array contains aliases and the view names to
	 * use when rendering the links.
	 * 分页链接通过视图来渲染，以配置其外观。该数组包含用于渲染链接的别名和视图名称。
	 *
	 * Within each view, the Pager object will be available as $pager,
	 * and the desired group as $pagerGroup;
	 * 在每个视图中,Pager对象将可用作为$ Pager,而所需的组则为$ pagerGroup;
	 *
	 * @var array<string, string>
	 */
	public $templates = [
		'default_full'   => 'CodeIgniter\Pager\Views\default_full',
		'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
		'default_head'   => 'CodeIgniter\Pager\Views\default_head',
	];

	/**
	 * --------------------------------------------------------------------------
	 * Items Per Page	每页数量
	 * --------------------------------------------------------------------------
	 *
	 * The default number of results shown in a single page.
	 * 在单个页面中显示的默认结果数
	 *
	 * @var integer
	 */
	public $perPage = 20;
}
