<?php
/**
 * CodeIgniter，命令，生成器，控制器生成器
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
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton controller file.
 * 生成skeleton控制器文件。
 */
class ControllerGenerator extends BaseCommand
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
	protected $name = 'make:controller';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new controller file.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:controller <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The controller class name.',
	];

	/**
	 * The Command's Options
	 * 命令选项
	 *
	 * @var array
	 */
	protected $options = [
		'--bare'      => 'Extends from CodeIgniter\Controller instead of BaseController.',
		'--restful'   => 'Extends from a RESTful resource, Options: [controller, presenter]. Default: "controller".',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserController).',
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
		$this->component = 'Controller';
		$this->directory = 'Controllers';
		$this->template  = 'controller.tpl.php';

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
		$bare = $this->getOption('bare');
		$rest = $this->getOption('restful');

		$useStatement = trim(APP_NAMESPACE, '\\') . '\Controllers\BaseController';
		$extends      = 'BaseController';

		// Gets the appropriate parent class to extend.
		// 获取适当的父类来扩展
		if ($bare || $rest)
		{
			if ($bare)
			{
				$useStatement = 'CodeIgniter\Controller';
				$extends      = 'Controller';
			}
			elseif ($rest)
			{
				$rest = is_string($rest) ? $rest : 'controller';

				if (! in_array($rest, ['controller', 'presenter'], true))
				{
					// @codeCoverageIgnoreStart
					$rest = CLI::prompt(lang('CLI.generator.parentClass'), ['controller', 'presenter'], 'required');
					CLI::newLine();
					// @codeCoverageIgnoreEnd
				}

				if ($rest === 'controller')
				{
					$useStatement = 'CodeIgniter\RESTful\ResourceController';
					$extends      = 'ResourceController';
				}
				elseif ($rest === 'presenter')
				{
					$useStatement = 'CodeIgniter\RESTful\ResourcePresenter';
					$extends      = 'ResourcePresenter';
				}
			}
		}

		return $this->parseTemplate(
			$class,
			['{useStatement}', '{extends}'],
			[$useStatement, $extends],
			['type' => $rest]
		);
	}
}
