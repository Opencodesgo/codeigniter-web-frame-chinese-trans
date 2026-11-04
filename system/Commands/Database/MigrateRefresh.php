<?php
/**
 * CodeIgniter，命令，数据库，迁移刷新
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

/**
 * Does a rollback followed by a latest to refresh the current state
 * of the database.
 * 回滚后，再执行最新操作以刷新数据库的当前状态。
 */
class MigrateRefresh extends BaseCommand
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
	protected $name = 'migrate:refresh';

	/**
	 * the Command's short description
	 * 命令的简短描述
	 *
	 * @var string
	 */
	protected $description = 'Does a rollback followed by a latest to refresh the current state of the database.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'migrate:refresh [options]';

	/**
	 * the Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'-n'    => 'Set migration namespace',
		'-g'    => 'Set database group',
		'--all' => 'Set latest for all namespace, will ignore (-n) option',
		'-f'    => 'Force command - this option allows you to bypass the confirmation question when running this command in a production environment',
	];

	/**
	 * Does a rollback followed by a latest to refresh the current state
	 * of the database.
	 * 回滚后执行最新操作可刷新数据库的当前状态
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$params['b'] = 0;

		if (ENVIRONMENT === 'production')
		{
			// @codeCoverageIgnoreStart
			$force = array_key_exists('f', $params) || CLI::getOption('f');

			if (! $force && CLI::prompt(lang('Migrations.refreshConfirm'), ['y', 'n']) === 'n')
			{
				return;
			}

			$params['f'] = null;
			// @codeCoverageIgnoreEnd
		}

		$this->call('migrate:rollback', $params);
		$this->call('migrate', $params);
	}
}
