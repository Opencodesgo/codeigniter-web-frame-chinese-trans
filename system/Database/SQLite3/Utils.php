<?php
/**
 * CodeIgniter，数据库，SQLite3，工具
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

use CodeIgniter\Database\BaseUtils;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Utils for SQLite3
 * SQLite3 工具。
 */
class Utils extends BaseUtils
{
	/**
	 * OPTIMIZE TABLE statement
	 * OPTIMIZE TABLE 语句
	 *
	 * @var string
	 */
	protected $optimizeTable = 'REINDEX %s';

	//--------------------------------------------------------------------

	/**
	 * Platform dependent version of the backup function.
	 * 备份功能的平台依赖版本
	 *
	 * @param array|null $prefs
	 *
	 * @return mixed
	 */
	public function _backup(array $prefs = null)
	{
		throw new DatabaseException('Unsupported feature of the database platform you are using.');
	}

	//--------------------------------------------------------------------
}
