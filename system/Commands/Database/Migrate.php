<?php
/**
 * CodeIgniter，命令，数据库，迁移
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use Throwable;

/**
 * Runs all new migrations.
 * 运行所有新迁移。
 */
class Migrate extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 在列出命令时，该组会被归入命令的同一类别。
	 *
	 * @var string
	 */
	protected $group = 'Database';

	/**
	 * The Command's name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'migrate';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Locates and runs all new migrations against the database.';

	/**
	 * the Command's usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'migrate [options]';

	/**
	 * the Command's Options
	 * 命令选项
	 *
	 * @var array
	 */
	protected $options = [
		'-n'    => 'Set migration namespace',
		'-g'    => 'Set database group',
		'--all' => 'Set for all namespaces, will ignore (-n) option',
	];

	/**
	 * Ensures that all migrations have been run.
	 * 确保所有迁移都已运行
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$runner = Services::migrations();
		$runner->clearCliMessages();

		CLI::write(lang('Migrations.latest'), 'yellow');

		$namespace = $params['n'] ?? CLI::getOption('n');
		$group     = $params['g'] ?? CLI::getOption('g');

		try
		{
			// Check for 'all' namespaces
			if (array_key_exists('all', $params) || CLI::getOption('all'))
			{
				$runner->setNamespace(null);
			}
			// Check for a specified namespace
			elseif ($namespace)
			{
				$runner->setNamespace($namespace);
			}

			if (! $runner->latest($group))
			{
				CLI::error(lang('Migrations.generalFault'), 'light_gray', 'red'); // @codeCoverageIgnore
			}

			$messages = $runner->getCliMessages();

			foreach ($messages as $message)
			{
				CLI::write($message);
			}

			CLI::write('Done migrations.', 'green');
		}
		// @codeCoverageIgnoreStart
		catch (Throwable $e)
		{
			$this->showError($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
