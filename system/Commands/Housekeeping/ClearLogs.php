<?php
/**
 * CodeIgniter，命令，Housekeeping，清除日志
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Housekeeping;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ClearLogs command.
 * 清除日志命令。
 */
class ClearLogs extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 命令列表中该组所包含的命令
	 *
	 * @var string
	 */
	protected $group = 'Housekeeping';

	/**
	 * The Command's name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'logs:clear';

	/**
	 * The Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Clears all log files.';

	/**
	 * The Command's usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'logs:clear [option';

	/**
	 * The Command's options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [
		'--force' => 'Force delete of all logs files without prompting.',
	];

	/**
	 * Actually execute a command.
	 * 实际运行命令
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$force = array_key_exists('force', $params) || CLI::getOption('force');

		if (! $force && CLI::prompt('Are you sure you want to delete the logs?', ['n', 'y']) === 'n')
		{
			// @codeCoverageIgnoreStart
			CLI::error('Deleting logs aborted.', 'light_gray', 'red');
			CLI::error('If you want, use the "-force" option to force delete all log files.', 'light_gray', 'red');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		helper('filesystem');

		if (! delete_files(WRITEPATH . 'logs', false, true))
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error in deleting the logs files.', 'light_gray', 'red');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		CLI::write('Logs cleared.', 'green');
		CLI::newLine();
	}
}
