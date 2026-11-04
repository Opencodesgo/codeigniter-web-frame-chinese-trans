<?php
/**
 * CodeIgniter，测试，模拟，模拟文件记录器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Log\Handlers\FileHandler;

/**
 * Class MockFileLogger
 * 模拟文件记录器类。
 *
 * Extends FileHandler, exposing some inner workings
 * 扩展FileHandler，公开一些内部工作原理。
 */
class MockFileLogger extends FileHandler
{
	/**
	 * Where would the log be written?
	 * 日志应该写在哪里？
	 */
	public $destination;

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->handles     = $config['handles'] ?? [];
		$this->destination = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;
	}
}
