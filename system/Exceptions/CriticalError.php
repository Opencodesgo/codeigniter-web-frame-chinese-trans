<?php
/**
 * CodeIgniter，异常，临界的错误
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
 * Error: Critical conditions, like component unavailable, etc.
 * 错误：关键情况，如组件不可用等。
 */
class CriticalError extends Error
{
}
