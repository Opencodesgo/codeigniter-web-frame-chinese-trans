<?php
/**
 * CodeIgniter，测试，模拟，模拟资源控制器
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

use CodeIgniter\RESTful\ResourceController;

class MockResourceController extends ResourceController
{
	public function getModel()
	{
		return $this->model;
	}

	public function getModelName()
	{
		return $this->modelName;
	}

	public function getFormat()
	{
		return $this->format;
	}
}
