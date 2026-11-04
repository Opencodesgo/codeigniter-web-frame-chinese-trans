<?php
/**
 * CodeIgniter，测试，模拟，模拟表
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

use BadMethodCallException;
use CodeIgniter\View\Table;

class MockTable extends Table
{
	// Override inaccessible protected method
	// 覆盖不可访问的保护方法
	public function __call($method, $params)
	{
		if (is_callable([$this, '_' . $method]))
		{
			return call_user_func_array([$this, '_' . $method], $params);
		}

		throw new BadMethodCallException('Method ' . $method . ' was not found');
	}
}
