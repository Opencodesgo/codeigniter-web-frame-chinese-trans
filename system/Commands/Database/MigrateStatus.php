<?php
/**
 * CodeIgniter，命令，数据库，迁移状态
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

/**
 * Displays a list of all migrations and whether they've been run or not.
 * 显示所有迁移的列表，以及它们是否已运行。
 */
class MigrateStatus extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 命令列表中该组所包含的命令
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
	protected $name = 'migrate:status';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'migrate:status [options]';

	/**
	 * the Command's Options
	 * 命令的选项
	 *
	 * @var array<string, string>
	 */
	protected $options = [
		'-g' => 'Set database group',
	];

	/**
	 * Namespaces to ignore when looking for migrations.
	 * 在寻找迁移时要忽略的命名空间
	 *
	 * @var string[]
	 */
	protected $ignoredNamespaces = [
		'CodeIgniter',
		'Config',
		'Kint',
		'Laminas\ZendFrameworkBridge',
		'Laminas\Escaper',
		'Psr\Log',
	];

	/**
	 * Displays a list of all migrations and whether they've been run or not.
	 * 显示所有迁移的列表，以及它们是否已运行。
	 *
	 * @param array<string, mixed> $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$runner = Services::migrations();
		$group  = $params['g'] ?? CLI::getOption('g');

		// Get all namespaces
		// 获取所有命名空间
		$namespaces = Services::autoloader()->getNamespace();

		// Collection of migration status
		// 迁移状态的收集
		$status = [];

		foreach ($namespaces as $namespace => $path)
		{
			if (ENVIRONMENT !== 'testing')
			{
				// Make Tests\\Support discoverable for testing
				$this->ignoredNamespaces[] = 'Tests\Support'; // @codeCoverageIgnore
			}

			if (in_array($namespace, $this->ignoredNamespaces, true))
			{
				continue;
			}

			if (APP_NAMESPACE !== 'App' && $namespace === 'App')
			{
				continue; // @codeCoverageIgnore
			}

			$migrations = $runner->findNamespaceMigrations($namespace);

			if (empty($migrations))
			{
				continue;
			}

			$history = $runner->getHistory((string) $group);
			ksort($migrations);

			foreach ($migrations as $uid => $migration)
			{
				$migrations[$uid]->name = mb_substr($migration->name, mb_strpos($migration->name, $uid . '_'));

				$date  = '---';
				$group = '---';
				$batch = '---';

				foreach ($history as $row)
				{
					// @codeCoverageIgnoreStart
					if ($runner->getObjectUid($row) !== $migration->uid)
					{
						continue;
					}

					$date  = date('Y-m-d H:i:s', $row->time);
					$group = $row->group;
					$batch = $row->batch;
					// @codeCoverageIgnoreEnd
				}

				$status[] = [
					$namespace,
					$migration->version,
					$migration->name,
					$group,
					$date,
					$batch,
				];
			}
		}

		if (! $status)
		{
			// @codeCoverageIgnoreStart
			CLI::error(lang('Migrations.noneFound'), 'light_gray', 'red');
			CLI::newLine();

			return;
			// @codeCoverageIgnoreEnd
		}

		$headers = [
			CLI::color(lang('Migrations.namespace'), 'yellow'),
			CLI::color(lang('Migrations.version'), 'yellow'),
			CLI::color(lang('Migrations.filename'), 'yellow'),
			CLI::color(lang('Migrations.group'), 'yellow'),
			CLI::color(str_replace(': ', '', lang('Migrations.on')), 'yellow'),
			CLI::color(lang('Migrations.batch'), 'yellow'),
		];

		CLI::table($status, $headers);
	}
}
