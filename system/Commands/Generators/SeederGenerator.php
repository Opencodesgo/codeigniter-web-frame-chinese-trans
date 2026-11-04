<?php
/**
 * CodeIgniter，命令，生成器，Seeder 发生器
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
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton seeder file.
 * 生成一个骨架种子文件。
 */
class SeederGenerator extends BaseCommand
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
	protected $name = 'make:seeder';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new seeder file.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:seeder <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The seeder class name.',
	];

	/**
	 * The Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserSeeder).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 * 实际执行命令
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Seeder';
		$this->directory = 'Database\Seeds';
		$this->template  = 'seeder.tpl.php';

		$this->execute($params);
	}
}
