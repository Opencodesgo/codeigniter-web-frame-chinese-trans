<?php
/**
 * CodeIgniter，CLI，控制台
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

use CodeIgniter\CodeIgniter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Console
 * 控制台
 */
class Console
{
	/**
	 * Main CodeIgniter instance.
	 * 主CodeIgniter实例
	 *
	 * @var CodeIgniter
	 */
	protected $app;

	//--------------------------------------------------------------------

	/**
	 * Console constructor.
	 * 控制台构造函数
	 *
	 * @param CodeIgniter $app
	 */
	public function __construct(CodeIgniter $app)
	{
		$this->app = $app;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the current command discovered on the CLI.
	 * 在CLI界面执行当前发现的命令
	 *
	 * @param boolean $useSafeOutput
	 *
	 * @return RequestInterface|Response|ResponseInterface|mixed
	 * @throws Exception
	 */
	public function run(bool $useSafeOutput = false)
	{
		$path = CLI::getURI() ?: 'list';

		// Set the path for the application to route to.
		// 设置应用程序要路由到的路径
		$this->app->setPath("ci{$path}");

		return $this->app->useSafeOutput($useSafeOutput)->run();
	}

	//--------------------------------------------------------------------

	/**
	 * Displays basic information about the Console.
	 * 显示控制台的基本信息
	 */
	public function showHeader()
	{
		CLI::write(sprintf('CodeIgniter v%s Command Line Tool - Server Time: %s UTC%s', CodeIgniter::CI_VERSION, date('Y-m-d H:i:s'), date('P')), 'green');
		CLI::newLine();
	}
}
