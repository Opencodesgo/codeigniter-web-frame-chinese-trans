<?php
/**
 * CodeIgniter，测试，模拟，模拟 CodeIgniter
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

use CodeIgniter\CodeIgniter;

class MockCodeIgniter extends CodeIgniter
{
	protected function callExit($code)
	{
		// Do not call exit() in testing.
	}
}
