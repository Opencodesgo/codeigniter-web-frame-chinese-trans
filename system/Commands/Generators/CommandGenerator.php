<?php
/**
 * CodeIgniter，命令，生成器，命令发生器
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
 * Generates a skeleton command file.
 * 生成skeleton命令文件。
 */
class CommandGenerator extends BaseCommand
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
	protected $name = 'make:command';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new spark command.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:command <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The command class name.',
	];

	/**
	 * The Command's Options
	 * 命令选项
	 *
	 * @var array
	 */
	protected $options = [
		'--command'   => 'The command name. Default: "command:name"',
		'--type'      => 'The command type. Options [basic, generator]. Default: "basic".',
		'--group'     => 'The command group. Default: [basic -> "CodeIgniter", generator -> "Generators"].',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserCommand).',
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
		$this->component = 'Command';
		$this->directory = 'Commands';
		$this->template  = 'command.tpl.php';

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
		$command = $this->getOption('command');
		$group   = $this->getOption('group');
		$type    = $this->getOption('type');

		$command = is_string($command) ? $command : 'command:name';
		$group   = is_string($group) ? $group : 'CodeIgniter';
		$type    = is_string($type) ? $type : 'basic';

		if (! in_array($type, ['basic', 'generator'], true))
		{
			// @codeCoverageIgnoreStart
			$type = CLI::prompt(lang('CLI.generator.commandType'), ['basic', 'generator'], 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		if ($type === 'generator')
		{
			$group = 'Generators';
		}

		return $this->parseTemplate(
			$class,
			['{group}', '{command}'],
			[$group, $command],
			['type' => $type]
		);
	}
}
