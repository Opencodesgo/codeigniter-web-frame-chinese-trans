<?php
/**
 * 配置，蜜罐
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Honeypot extends BaseConfig
{
	/**
	 * Makes Honeypot visible or not to human
	 * 让蜜罐对人类可见或不可见
	 *
	 * @var boolean
	 */
	public $hidden = true;

	/**
	 * Honeypot Label Content
	 * 蜜罐标签内容
	 *
	 * @var string
	 */
	public $label = 'Fill This Field';

	/**
	 * Honeypot Field Name
	 * 蜜罐字段名称
	 *
	 * @var string
	 */
	public $name = 'honeypot';

	/**
	 * Honeypot HTML Template
	 * 蜜罐HTML模板
	 *
	 * @var string
	 */
	public $template = '<label>{label}</label><input type="text" name="{name}" value=""/>';

	/**
	 * Honeypot container
	 * 蜜罐容器
	 *
	 * @var string
	 */
	public $container = '<div style="display:none">{template}</div>';
}
