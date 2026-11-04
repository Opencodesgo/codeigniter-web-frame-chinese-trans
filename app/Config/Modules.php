<?php
/**
 * 配置，模块
 */

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
	/**
	 * --------------------------------------------------------------------------
	 * Enable Auto-Discovery?	启用自动发现?
	 * --------------------------------------------------------------------------
	 *
	 * If true, then auto-discovery will happen across all elements listed in
	 * $activeExplorers below. If false, no auto-discovery will happen at all,
	 * giving a slight performance boost.
	 * 如果为真，则会自动发现以下 $activeExplorers 中列出的所有元素。
	 * 如果为假，没有自动发现,就会发生轻微的性能提升。
	 *
	 * @var boolean
	 */
	public $enabled = true;

	/**
	 * --------------------------------------------------------------------------
	 * Enable Auto-Discovery Within Composer Packages?	在编写器包中启用自动发现功能？
	 * --------------------------------------------------------------------------
	 *
	 * If true, then auto-discovery will happen across all namespaces loaded
	 * by Composer, as well as the namespaces configured locally.
	 * 如果为真，Composer 所加载的所有命名空间以及本地配置的命名空间都将进行自动发现。
	 *
	 * @var boolean
	 */
	public $discoverInComposer = true;

	/**
	 * --------------------------------------------------------------------------
	 * Auto-Discovery Rules	自动发现规则
	 * --------------------------------------------------------------------------
	 *
	 * Aliases list of all discovery classes that will be active and used during
	 * the current application request.
	 * 当前应用程序请求期间将被激活并使用的所有发现类的别名列表。
	 *
	 * If it is not listed, only the base application elements will be used.
	 * 如果没有列出，则只使用基本应用程序元素。
	 *
	 * @var string[]
	 */
	public $aliases = [
		'events',
		'filters',
		'registrars',
		'routes',
		'services',
	];
}
