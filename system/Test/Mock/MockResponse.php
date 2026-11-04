<?php
/**
 * CodeIgniter，测试，模拟，模拟响应
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

use CodeIgniter\HTTP\Response;

/**
 * Class MockResponse
 * 模拟响应类。
 */
class MockResponse extends Response
{
	/**
	 * If true, will not write output. Useful during testing.
	 * 如果为true，则不输出。在测试期间非常有用。
	 *
	 * @var boolean
	 */
	protected $pretend = true;

	// for testing
	public function getPretend()
	{
		return $this->pretend;
	}

	// artificial error for testing
	// 人为测试误差
	public function misbehave()
	{
		$this->statusCode = 0;
	}
}
