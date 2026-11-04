<?php
/**
 * CodeIgniter，命令，数据库，创建数据库
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
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\SQLite3\Connection;
use Config\Database;
use Throwable;

/**
 * Creates a new database.
 * 创建新数据库。
 */
class CreateDatabase extends BaseCommand
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
	protected $name = 'db:create';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Create a new database schema.';

	/**
	 * the Command's usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'db:create <db_name> [options]';

	/**
	 * The Command's arguments
	 * 命令参数
	 *
	 * @var array<string, string>
	 */
	protected $arguments = [
		'db_name' => 'The database name to use',
	];

	/**
	 * The Command's options
	 * 命令选项
	 *
	 * @var array<string, string>
	 */
	protected $options = [
		'--ext' => 'File extension of the database file for SQLite3. Can be `db` or `sqlite`. Defaults to `db`.',
	];

	/**
	 * Creates a new database.
	 * 创建新的数据库
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$name = array_shift($params);

		if (empty($name))
		{
			$name = CLI::prompt('Database name', null, 'required'); // @codeCoverageIgnore
		}

		$db = Database::connect();

		try
		{
			// Special SQLite3 handling
			if ($db instanceof Connection)
			{
				$config = config('Database');
				$group  = ENVIRONMENT === 'testing' ? 'tests' : $config->defaultGroup;
				$ext    = $params['ext'] ?? CLI::getOption('ext') ?? 'db';

				if (! in_array($ext, ['db', 'sqlite'], true))
				{
					$ext = CLI::prompt('Please choose a valid file extension', ['db', 'sqlite']); // @codeCoverageIgnore
				}

				if ($name !== ':memory:')
				{
					$name = str_replace(['.db', '.sqlite'], '', $name) . ".{$ext}";
				}

				$config->{$group}['DBDriver'] = 'SQLite3';
				$config->{$group}['database'] = $name;

				if ($name !== ':memory:')
				{
					$dbName = strpos($name, DIRECTORY_SEPARATOR) === false ? WRITEPATH . $name : $name;

					if (is_file($dbName))
					{
						CLI::error("Database \"{$dbName}\" already exists.", 'light_gray', 'red');
						CLI::newLine();

						return;
					}

					unset($dbName);
				}

				// Connect to new SQLite3 to create new database,
				// then reset the altered Config\Database instance
				// 连接到新的 SQLite3 以创建新数据库，然后重置已修改的 Config\Database 实例。
				$db = Database::connect(null, false);
				$db->connect();
				Factories::reset('config');

				if (! is_file($db->getDatabase()) && $name !== ':memory:')
				{
					// @codeCoverageIgnoreStart
					CLI::error('Database creation failed.', 'light_gray', 'red');
					CLI::newLine();

					return;
					// @codeCoverageIgnoreEnd
				}
			}
			else
			{
				if (! Database::forge()->createDatabase($name))
				{
					// @codeCoverageIgnoreStart
					CLI::error('Database creation failed.', 'light_gray', 'red');
					CLI::newLine();

					return;
					// @codeCoverageIgnoreEnd
				}
			}

			CLI::write("Database \"{$name}\" successfully created.", 'green');
			CLI::newLine();
		}
		catch (Throwable $e)
		{
			$this->showError($e);
		}
	}
}
