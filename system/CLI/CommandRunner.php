<?php
/**
 * CodeIgniter，CLI，命令运行器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\Controller;
use Config\Services;
use ReflectionException;

/**
 * Command runner
 * 命令运行器。
 */
class CommandRunner extends Controller
{
	/**
	 * The Command Manager
	 * 命令管理器
	 *
	 * @var Commands
	 */
	protected $commands;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 */
	public function __construct()
	{
		$this->commands = Services::commands();
	}

	/**
	 * We map all un-routed CLI methods through this function
	 * so we have the chance to look for a Command first.
	 * 我们通过此函数映射所有未路由的CLI方法，以便能够先查找命令。
	 *
	 * @param string $method
	 * @param array  ...$params
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function _remap($method, ...$params)
	{
		// The first param is usually empty, so scrap it.
		// 第一个参数通常是空的，所以省略它。
		if (empty($params[0]))
		{
			array_shift($params);
		}

		return $this->index($params);
	}

	//--------------------------------------------------------------------

	/**
	 * Default command.
	 * 默认命令
	 *
	 * @param array $params
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function index(array $params)
	{
		$command = array_shift($params);

		if (is_null($command))
		{
			$command = 'list';
		}

		return service('commands')->run($command, $params);
	}

	/**
	 * Allows access to the current commands that have been found.
	 * 允许访问已找到的当前命令
	 *
	 * @return array
	 */
	public function getCommands(): array
	{
		return $this->commands->getCommands();
	}
}
