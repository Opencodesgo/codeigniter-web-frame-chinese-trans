<?php
/**
 * CodeIgniter，命令，帮助
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

/**
 * CI Help command for the spark script.
 * CI帮助命令，用于spark脚本。
 *
 * Lists the basic usage information for the spark script,
 * and provides a way to list help for other commands.
 * 列出 spark 脚本的基本使用信息，并提供查看其他命令帮助的方法。
 */
class Help extends BaseCommand
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
	protected $name = 'help';

	/**
	 * the Command's short description
	 * 命令的简短描述
	 *
	 * @var string
	 */
	protected $description = 'Displays basic usage information.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'help command_name';

	/**
	 * the Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'command_name' => 'The command name [default: "help"]',
	];

	/**
	 * the Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for spark commands.
	 * 提供spark命令帮助
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$command  = array_shift($params);
		$command  = $command ?? 'help';
		$commands = $this->commands->getCommands();

		if (! $this->commands->verifyCommand($command, $commands))
		{
			return;
		}

		$class = new $commands[$command]['class']($this->logger, $this->commands);
		$class->showHelp();
	}
}
