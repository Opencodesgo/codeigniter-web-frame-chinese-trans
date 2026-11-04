<?php
/**
 * CodeIgniter，命令，生成器，配置生成器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton config file.
 * 生成一个框架配置文件
 */
class ConfigGenerator extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 * 命令组
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's Name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'make:config';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new config file.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:config <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The config class name.',
	];

	/**
	 * The Command's Options
	 * 命令选项
	 *
	 * @var array
	 */
	protected $options = [
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserConfig).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 * 实际执行命令
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Config';
		$this->directory = 'Config';
		$this->template  = 'config.tpl.php';

		$this->execute($params);
	}

	/**
	 * Prepare options and do the necessary replacements.
	 * 准备备选方案并进行必要的替换
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepare(string $class): string
	{
		$namespace = $this->getOption('namespace') ?? APP_NAMESPACE;

		if ($namespace === APP_NAMESPACE)
		{
			$class = substr($class, strlen($namespace . '\\'));
		}

		return $this->parseTemplate($class);
	}
}
