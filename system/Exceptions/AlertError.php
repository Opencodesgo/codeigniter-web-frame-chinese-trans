<?php
/**
 * CodeIgniter，异常，警告错误
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Exceptions;

use Error;

/**
 * Error: Action must be taken immediately (system/db down, etc)
 * 错误：必须立即采取行动（系统/数据库关闭等）
 */
class AlertError extends Error
{
}
