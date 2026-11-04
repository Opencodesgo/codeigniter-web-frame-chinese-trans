<?php
/**
 * CodeIgniter，数据库，异常，数据库异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Exceptions;

use Error;

class DatabaseException extends Error implements ExceptionInterface
{
	/**
	 * Exit status code
	 * 退出状态代码
	 *
	 * @var integer
	 */
	protected $code = 8;
}
