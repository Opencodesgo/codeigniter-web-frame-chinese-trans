<?php
/**
 * CodeIgniter，命令，生成器，Scaffold 发生器
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
 * Generates a complete set of scaffold files.
 * 生成一组完整的scaffold文件。
 */
class ScaffoldGenerator extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 * 命令组
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
	protected $name = 'make:scaffold';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a complete set of scaffold files.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:scaffold <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The class name',
	];

	/**
	 * The Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--bare'      => 'Add the "--bare" option to controller component.',
		'--restful'   => 'Add the "--restful" option to controller component.',
		'--table'     => 'Add the "--table" option to the model component.',
		'--dbgroup'   => 'Add the "--dbgroup" option to model component.',
		'--return'    => 'Add the "--return" option to the model component.',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name.',
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
		$this->params = $params;

		$options = [];

		if ($this->getOption('namespace'))
		{
			$options['namespace'] = $this->getOption('namespace');
		}

		if ($this->getOption('suffix'))
		{
			$options['suffix'] = null;
		}

		if ($this->getOption('force'))
		{
			$options['force'] = null;
		}

		$controllerOpts = [];

		if ($this->getOption('bare'))
		{
			$controllerOpts['bare'] = null;
		}
		elseif ($this->getOption('restful'))
		{
			$controllerOpts['restful'] = $this->getOption('restful');
		}

		$modelOpts = [
			'table'   => $this->getOption('table'),
			'dbgroup' => $this->getOption('dbgroup'),
			'return'  => $this->getOption('return'),
		];

		$class = $params[0] ?? CLI::getSegment(2);

		// Call those commands!
		// 调用这些命令！
		$this->call('make:controller', array_merge([$class], $controllerOpts, $options));
		$this->call('make:model', array_merge([$class], $modelOpts, $options));
		$this->call('make:migration', array_merge([$class], $options));
		$this->call('make:seeder', array_merge([$class], $options));
	}
}
