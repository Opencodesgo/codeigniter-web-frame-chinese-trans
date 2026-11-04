<?php
/**
 * CodeIgniter，数据库，SQLite3，构建器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\BaseBuilder;

/**
 * Builder for SQLite3
 * SQLite3 构建器。
 */
class Builder extends BaseBuilder
{
	/**
	 * Default installs of SQLite typically do not
	 * support limiting delete clauses.
	 * SQLite的默认安装通常不支持限制删除语句
	 *
	 * @var boolean
	 */
	protected $canLimitDeletes = false;

	/**
	 * Default installs of SQLite do no support
	 * limiting update queries in combo with WHERE.
	 * 默认安装的 SQLite 不支持在 WHERE 与更新查询结合时进行限制
	 *
	 * @var boolean
	 */
	protected $canLimitWhereUpdates = false;

	/**
	 * ORDER BY random keyword
	 * 按随机关键字排序
	 *
	 * @var array
	 */
	protected $randomKeyword = [
		'RANDOM()',
	];

	/**
	 * @var array
	 */
	protected $supportedIgnoreStatements = [
		'insert' => 'OR IGNORE',
	];

	//--------------------------------------------------------------------

	/**
	 * Replace statement
	 * 替换语句。
	 *
	 * Generates a platform-specific replace string from the supplied data
	 * 从提供的数据生成特定于平台的替换字符串。
	 *
	 * @param string $table  the table name
	 * @param array  $keys   the insert keys
	 * @param array  $values the insert values
	 *
	 * @return string
	 */
	protected function _replace(string $table, array $keys, array $values): string
	{
		return 'INSERT OR ' . parent::_replace($table, $keys, $values);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate statement
	 * 截断声明。
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 * 从提供的数据生成特定于平台的截断字符串。
	 *
	 * If the database does not support the TRUNCATE statement,
	 * then this method maps to 'DELETE FROM table'
	 * 如果数据库不支持 TRUNCATE 语句，则此方法映射为“DELETE FROM table”。
	 *
	 * @param  string $table
	 * @return string
	 */
	protected function _truncate(string $table): string
	{
		return 'DELETE FROM ' . $table;
	}
}
