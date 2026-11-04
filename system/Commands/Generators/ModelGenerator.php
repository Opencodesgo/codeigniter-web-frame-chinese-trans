<?php
/**
 * CodeIgniter，命令，生成器，模型发生器
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
 * Generates a skeleton Model file.
 * 生成一个骨架模型文件。
 */
class ModelGenerator extends BaseCommand
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
	protected $name = 'make:model';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new model file.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:model <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令的参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The model class name.',
	];

	/**
	 * The Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--table'     => 'Supply a table name. Default: "the lowercased plural of the class name".',
		'--dbgroup'   => 'Database group to use. Default: "default".',
		'--return'    => 'Return type, Options: [array, object, entity]. Default: "array".',
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserModel).',
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
		$this->component = 'Model';
		$this->directory = 'Models';
		$this->template  = 'model.tpl.php';

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
		$table   = $this->getOption('table');
		$DBGroup = $this->getOption('dbgroup');
		$return  = $this->getOption('return');

		$baseClass = strtolower(str_replace(trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\') . '\\', '', $class));
		$baseClass = strpos($baseClass, 'model') ? str_replace('model', '', $baseClass) : $baseClass;

		$table   = is_string($table) ? $table : plural($baseClass);
		$DBGroup = is_string($DBGroup) ? $DBGroup : 'default';
		$return  = is_string($return) ? $return : 'array';

		if (! in_array($return, ['array', 'object', 'entity'], true))
		{
			// @codeCoverageIgnoreStart
			$return = CLI::prompt(lang('CLI.generator.returnType'), ['array', 'object', 'entity'], 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		if ($return === 'entity')
		{
			$return = str_replace('Models', 'Entities', $class);

			if ($pos = strpos($return, 'Model'))
			{
				$return = substr($return, 0, $pos);

				if ($this->getOption('suffix'))
				{
					$return .= 'Entity';
				}
			}

			$this->call('make:entity', array_merge([$baseClass], $this->params));
		}

		return $this->parseTemplate($class, ['{table}', '{DBGroup}', '{return}'], [$table, $DBGroup, $return]);
	}
}
