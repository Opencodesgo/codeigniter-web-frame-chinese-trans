<?php
/**
 * CodeIgniter，数据库，MySQLi，工具
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

use CodeIgniter\Database\BaseUtils;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Utils for MySQLi
 * MySQLi的Utils。
 */
class Utils extends BaseUtils
{
	/**
	 * List databases statement
	 * 列表数据库语句
	 *
	 * @var string
	 */
	protected $listDatabases = 'SHOW DATABASES';

	/**
	 * OPTIMIZE TABLE statement
	 * 优化表语句
	 *
	 * @var string
	 */
	protected $optimizeTable = 'OPTIMIZE TABLE %s';

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
