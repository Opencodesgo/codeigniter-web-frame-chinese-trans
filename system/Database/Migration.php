<?php
/**
 * CodeIgniter，数据库，迁移
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use Config\Database;

/**
 * Class Migration
 * 类迁移。
 */
abstract class Migration
{
	/**
	 * The name of the database group to use.
	 * 要使用的数据库组的名称
	 *
	 * @var string
	 */
	protected $DBGroup;

	/**
	 * Database Connection instance
	 * 数据库连接实例
	 *
	 * @var ConnectionInterface
	 */
	protected $db;

	/**
	 * Database Forge instance.
	 * 数据库伪造实例
	 *
	 * @var Forge
	 */
	protected $forge;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param Forge $forge
	 */
	public function __construct(Forge $forge = null)
	{
		$this->forge = ! is_null($forge) ? $forge : Database::forge($this->DBGroup ?? config('Database')->defaultGroup);

		$this->db = $this->forge->getConnection();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the database group name this migration uses.
	 * 返回此迁移使用的数据库组名称
	 *
	 * @return string
	 */
	public function getDBGroup(): ?string
	{
		return $this->DBGroup;
	}

	//--------------------------------------------------------------------

	/**
	 * Perform a migration step.
	 * 执行迁移步骤
	 */
	abstract public function up();

	//--------------------------------------------------------------------

	/**
	 * Revert a migration step.
	 * 恢复迁移步骤
	 */
	abstract public function down();

	//--------------------------------------------------------------------
}
