<?php
/**
 * CodeIgniter，命令，数据库，Seed
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
use CodeIgniter\Database\Seeder;
use Config\Database;
use Throwable;

/**
 * Runs the specified Seeder file to populate the database
 * with some data.
 * 运行指定的 Seeder 文件，以向数据库中填充一些数据。
 */
class Seed extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 在列出命令时，该组被归为命令的同一类别。
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
	protected $name = 'db:seed';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Runs the specified seeder to populate known data into the database.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'db:seed <seeder_name>';

	/**
	 * the Command's Arguments
	 * 命令的参数
	 *
	 * @var array<string, string>
	 */
	protected $arguments = [
		'seeder_name' => 'The seeder name to run',
	];

	/**
	 * Passes to Seeder to populate the database.
	 * 传递给seed以填充数据库
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$seeder   = new Seeder(new Database());
		$seedName = array_shift($params);

		if (empty($seedName))
		{
			$seedName = CLI::prompt(lang('Migrations.migSeeder'), null, 'required'); // @codeCoverageIgnore
		}

		try
		{
			$seeder->call($seedName);
		}
		catch (Throwable $e)
		{
			$this->showError($e);
		}
	}
}
