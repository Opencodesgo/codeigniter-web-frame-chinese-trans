<?php
/**
 * CodeIgniter，配置，工厂
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
 * Factories Configuration file.
 * 工厂配置文件。
 *
 * Provides overriding directives for how
 * Factories should handle discovery and
 * instantiation of specific components.
 * Each property should correspond to the
 * lowercase, plural component name.
 * 提供用于指导工厂如何发现和实例化特定组件的覆盖性指令。
 * 每个属性应对应小写形式的复数组件名称。
 */
class Factory extends BaseConfig
{
	/**
	 * Supplies a default set of options to merge for
	 * all unspecified factory components.
	 * 为所有未指定的工厂组件提供一组默认的合并选项
	 *
	 * @var array
	 */
	public static $default = [
		'component'  => null,
		'path'       => null,
		'instanceOf' => null,
		'getShared'  => true,
		'preferApp'  => true,
	];

	/**
	 * Specifies that Models should always favor child
	 * classes to allow easy extension of module Models.
	 * 规定模型应始终优先使用子类，以便轻松扩展模块模型。
	 *
	 * @var array
	 */
	public $models = [
		'preferApp' => true,
	];
}
