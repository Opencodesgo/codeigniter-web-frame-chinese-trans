<?php
/**
 * CodeIgniter，命令，生成器，迁移创建
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

/**
 * Deprecated class for the migration creation command.
 * 迁移创建命令的弃用类。
 *
 * @deprecated Use make:command instead.
 *
 * @codeCoverageIgnore
 */
class MigrateCreate extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 命令列表中该组所包含的命令
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'migrate:create';

	/**
	 * The Command's short description
	 * 命令的简短描述
	 *
	 * @var string
	 */
	protected $description = '[DEPRECATED] Creates a new migration file. Please use "make:migration" instead.';

	/**
	 * The Command's usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'migrate:create <name> [options]';

	/**
	 * The Command's arguments.
	 * 命令的参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The migration file name.',
	];

	/**
	 * The Command's options.
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'-n'      => 'Set root namespace. Defaults to APP_NAMESPACE',
		'--force' => 'Force overwrite existing files.',
	];

	/**
	 * Actually execute a command.
	 * 实际执行命令
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		// Resolve arguments before passing to make:migration
		// 在传递给make:migration之前解决参数
		$params[0] = $params[0] ?? CLI::getSegment(2);

		$params['namespace'] = $params['namespace'] ?? CLI::getOption('namespace') ?? APP_NAMESPACE;

		if (array_key_exists('force', $params) || CLI::getOption('force'))
		{
			$params['force'] = null;
		}

		$this->call('make:migration', $params);
	}
}
