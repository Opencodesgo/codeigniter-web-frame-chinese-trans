<?php
/**
 * CodeIgniter，命令，工具，命名空间
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;

/**
 * Lists namespaces set in Config\Autoload with their
 * full server path. Helps you to verify that you have
 * the namespaces setup correctly.
 * 列出 Config\Autoload 中设置的命名空间及其完整的服务器路径。
 * 有助于您验证命名空间是否已正确配置。
 */
class Namespaces extends BaseCommand
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
	protected $name = 'namespaces';

	/**
	 * the Command's short description
	 * 命令的简短描述
	 *
	 * @var string
	 */
	protected $description = 'Verifies your namespaces are setup correctly.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'namespaces';

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
	protected $options = [];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 * 显示spark cli脚本本身的帮助
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$config = new Autoload();

		$tbody = [];
		foreach ($config->psr4 as $ns => $path)
		{
			$path = realpath($path) ?: $path;

			$tbody[] = [
				$ns,
				realpath($path) ?: $path,
				is_dir($path) ? 'Yes' : 'MISSING',
			];
		}

		$thead = [
			'Namespace',
			'Path',
			'Found?',
		];

		CLI::table($tbody, $thead);
	}
}
