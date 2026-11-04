<?php
/**
 * CodeIgniter，数据库，异常，异常接口
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

/**
 * Provides a domain-level interface for broad capture
 * of all database-related exceptions.
 * 提供一个域级接口，用于广泛捕获所有与数据库相关的异常。
 *
 * catch (\CodeIgniter\Database\Exceptions\ExceptionInterface) { ... }
 */
interface ExceptionInterface extends \CodeIgniter\Exceptions\ExceptionInterface
{
}
