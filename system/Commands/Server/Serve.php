<?php
/**
 * CodeIgniter，命令，服务，Serve
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Server;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Launch the PHP development server
 * 启动PHP开发服务器。
 *
 * Not testable, as it throws phpunit for a loop :-/
 *
 * @codeCoverageIgnore
 */
class Serve extends BaseCommand
{
	/**
	 * Group
	 * 组
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * Name
	 * 名称
	 *
	 * @var string
	 */
	protected $name = 'serve';

	/**
	 * Description
	 * 描述
	 *
	 * @var string
	 */
	protected $description = 'Launches the CodeIgniter PHP-Development Server.';

	/**
	 * Usage
	 * 用法
	 *
	 * @var string
	 */
	protected $usage = 'serve';

	/**
	 * Arguments
	 * 参数
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The current port offset.
	 * 当前端口偏移量
	 *
	 * @var integer
	 */
	protected $portOffset = 0;

	/**
	 * The max number of ports to attempt to serve from
	 * 最大数量的端口
	 *
	 * @var integer
	 */
	protected $tries = 10;

	/**
	 * Options
	 * 选项
	 *
	 * @var array
	 */
	protected $options = [
		'--php'  => 'The PHP Binary [default: "PHP_BINARY"]',
		'--host' => 'The HTTP Host [default: "localhost"]',
		'--port' => 'The HTTP Host Port [default: "8080"]',
	];

	/**
	 * Run the server
	 * 运行服务
	 *
	 * @param array $params Parameters
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		// Collect any user-supplied options and apply them.
		// 收集所有用户提供的选项并应用它们
		$php  = escapeshellarg(CLI::getOption('php') ?? PHP_BINARY);
		$host = CLI::getOption('host') ?? 'localhost';
		$port = (int) (CLI::getOption('port') ?? 8080) + $this->portOffset;

		// Get the party started.
		CLI::write('CodeIgniter development server started on http://' . $host . ':' . $port, 'green');
		CLI::write('Press Control-C to stop.');

		// Set the Front Controller path as Document Root.
		$docroot = escapeshellarg(FCPATH);

		// Mimic Apache's mod_rewrite functionality with user settings.
		$rewrite = escapeshellarg(__DIR__ . '/rewrite.php');

		// Call PHP's built-in webserver, making sure to set our
		// base path to the public folder, and to use the rewrite file
		// to ensure our environment is set and it simulates basic mod_rewrite.
		passthru($php . ' -S ' . $host . ':' . $port . ' -t ' . $docroot . ' ' . $rewrite, $status);

		if ($status && $this->portOffset < $this->tries)
		{
			$this->portOffset++;

			$this->run($params);
		}
	}
}
