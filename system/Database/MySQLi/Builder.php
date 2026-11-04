<?php
/**
 * CodeIgniter，数据库，MySQLi，构建器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseBuilder;

/**
 * Builder for MySQLi
 * MySQLi的构建器。
 */
class Builder extends BaseBuilder
{
	/**
	 * Identifier escape character
	 * 标识符转义字符
	 *
	 * @var string
	 */
	protected $escapeChar = '`';

	/**
	 * Specifies which sql statements
	 * support the ignore option.
	 * 指定哪些 SQL 语句支持 ignore 选项
	 *
	 * @var array
	 */
	protected $supportedIgnoreStatements = [
		'update' => 'IGNORE',
		'insert' => 'IGNORE',
		'delete' => 'IGNORE',
	];

	/**
	 * FROM tables
	 * FROM 表格
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 * 根据需要在 FROM 子句中使用分组表，以避免运算符优先级的混淆。
	 *
	 * Note: This is only used (and overridden) by MySQL.
	 *
	 * @return string
	 */
	protected function _fromTables(): string
	{
		if (! empty($this->QBJoin) && count($this->QBFrom) > 1)
		{
			return '(' . implode(', ', $this->QBFrom) . ')';
		}

		return implode(', ', $this->QBFrom);
	}
}
