<?php
/**
 * CodeIgniter，命令，生成器，验证发生器
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
 * Generates a skeleton Validation file.
 * 生成一个骨架验证文件。
 */
class ValidationGenerator extends BaseCommand
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
	protected $name = 'make:validation';

	/**
	 * The Command's Description
	 * 命令描述
	 *
	 * @var string
	 */
	protected $description = 'Generates a new validation file.';

	/**
	 * The Command's Usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'make:validation <name> [options]';

	/**
	 * The Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The validation class name.',
	];

	/**
	 * The Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserValidation).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 * 实际运行命令
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'Validation';
		$this->directory = 'Validation';
		$this->template  = 'validation.tpl.php';

		$this->execute($params);
	}
}
