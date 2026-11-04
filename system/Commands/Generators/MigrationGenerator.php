<?php
/**
 * CodeIgniter，命令，生成器，迁移发生器
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
 * Generates a skeleton migration file.
 * 生成骨架迁移文件。
 */
class MigrationGenerator extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 * 命令分组
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
	protected $name = 'make:migration';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new migration file.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:migration <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令的参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The migration class name.',
	];

	/**
	 * The Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--session'   => 'Generates the migration file for database sessions.',
		'--table'     => 'Table name to use for database sessions. Default: "ci_sessions".',
		'--dbgroup'   => 'Database group to use for database sessions. Default: "default".',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserMigration).',
	];

	/**
	 * Actually execute a command.
	 * 实际执行命令
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Migration';
		$this->directory = 'Database\Migrations';
		$this->template  = 'migration.tpl.php';

		if (array_key_exists('session', $params) || CLI::getOption('session'))
		{
			$table     = $params['table'] ?? CLI::getOption('table') ?? 'ci_sessions';
			$params[0] = "_create_{$table}_table";
		}

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
		$data['session'] = false;

		if ($this->getOption('session'))
		{
			$table   = $this->getOption('table');
			$DBGroup = $this->getOption('dbgroup');

			$data['session'] = true;
			$data['table']   = is_string($table) ? $table : 'ci_sessions';
			$data['DBGroup'] = is_string($DBGroup) ? $DBGroup : 'default';
			$data['matchIP'] = config('App')->sessionMatchIP;
		}

		return $this->parseTemplate($class, [], [], $data);
	}

	/**
	 * Change file basename before saving.
	 * 保存前更改文件名称
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function basename(string $filename): string
	{
		return gmdate(config('Migrations')->timestampFormat) . basename($filename);
	}
}
