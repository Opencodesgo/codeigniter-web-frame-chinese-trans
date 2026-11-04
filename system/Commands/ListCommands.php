<?php
/**
 * CodeIgniter，命令，列表命令
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CI Help command for the spark script.
 * CI帮助命令，用于spark脚本。
 *
 * Lists the basic usage information for the spark script,
 * and provides a way to list help for other commands.
 * 列出 spark 脚本的基本使用信息，并提供查看其他命令帮助的方法。
 */
class ListCommands extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 命令列表中该组所包含的命令
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * The Command's name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'list';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Lists the available commands.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'list';

	/**
	 * the Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * the Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--simple' => 'Prints a list of the commands with no other info',
	];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 * 显示spark cli脚本本身的帮助
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$commands = $this->commands->getCommands();
		ksort($commands);

		// Check for 'simple' format
		return array_key_exists('simple', $params) || CLI::getOption('simple')
			? $this->listSimple($commands)
			: $this->listFull($commands);
	}

	/**
	 * Lists the commands with accompanying info.
	 * 列出命令和附带信息
	 *
	 * @param array $commands
	 */
	protected function listFull(array $commands)
	{
		// Sort into buckets by group
		// 按组分类
		$groups = [];

		foreach ($commands as $title => $command)
		{
			if (! isset($groups[$command['group']]))
			{
				$groups[$command['group']] = [];
			}

			$groups[$command['group']][$title] = $command;
		}

		$length = max(array_map('strlen', array_keys($commands)));

		ksort($groups);

		// Display it all...
		foreach ($groups as $group => $commands)
		{
			CLI::write($group, 'yellow');
			foreach ($commands as $name => $command)
			{
				$name   = $this->setPad($name, $length, 2, 2);
				$output = CLI::color($name, 'green');
				if (isset($command['description']))
				{
					$output .= CLI::wrap($command['description'], 125, strlen($name));
				}
				CLI::write($output);
			}

			end($groups);

			if ($group !== key($groups))
			{
				CLI::newLine();
			}
		}
	}

	/**
	 * Lists the commands only.
	 * 仅列出命令
	 *
	 * @param array $commands
	 */
	protected function listSimple(array $commands)
	{
		foreach ($commands as $title => $command)
		{
			CLI::write($title);
		}
	}
}
