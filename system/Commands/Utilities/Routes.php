<?php
/**
 * CodeIgniter，命令，工具，路由
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Lists all of the user-defined routes. This will include any Routes files
 * that can be discovered, but will NOT include any routes that are not defined
 * in a routes file, but are instead discovered through auto-routing.
 * 列出所有用户自定义的路由。这将包括所有可被发现的 Routes 文件，
 * 但不会包含那些未在 routes 文件中定义，而是通过自动路由发现的路由。
 */
class Routes extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 在列出命令时，该组被归为命令的同一类别。
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * The Command's name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'routes';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Displays all of user-defined routes. Does NOT display auto-detected routes.';

	/**
	 * the Command's usage
	 * 命令的用法
	 *
	 * @var string
	 */
	protected $usage = 'routes';

	/**
	 * the Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * the Command's Options
	 * 命令的选项
	 *
	 * @var array
	 */
	protected $options = [];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 * 显示spark cli脚本本身的帮助
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$collection = Services::routes(true);
		$methods    = [
			'get',
			'head',
			'post',
			'patch',
			'put',
			'delete',
			'options',
			'trace',
			'connect',
			'cli',
		];

		$tbody = [];
		foreach ($methods as $method)
		{
			$routes = $collection->getRoutes($method);

			foreach ($routes as $route => $handler)
			{
				// filter for strings, as callbacks aren't displayable
				// 筛选字符串，因为回调是不可显示的。
				if (is_string($handler))
				{
					$tbody[] = [
						strtoupper($method),
						$route,
						$handler,
					];
				}
			}
		}

		$thead = [
			'Method',
			'Route',
			'Handler',
		];

		CLI::table($tbody, $thead);
	}
}
