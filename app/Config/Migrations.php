<?php
/**
 * 配置，数据库迁移
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Migrations extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Enable/Disable Migrations	启用/禁用迁移
	 * --------------------------------------------------------------------------
	 *
	 * Migrations are enabled by default.
	 * 默认情况下，迁移是启用的。
	 *
	 * You should enable migrations whenever you intend to do a schema migration
	 * and disable it back when you're done.
	 * 当你打算进行模式迁移时，应启用迁移功能；完成后再将其禁用。
	 *
	 * @var boolean
	 */
	public $enabled = true;

	/**
	 * --------------------------------------------------------------------------
	 * Migrations Table		迁移表
	 * --------------------------------------------------------------------------
	 *
	 * This is the name of the table that will store the current migrations state.
	 * When migrations runs it will store in a database table which migration
	 * level the system is at. It then compares the migration level in this
	 * table to the $config['migration_version'] if they are not the same it
	 * will migrate up. This must be set.
	 * 这是用于存储当前迁移状态的表名。当迁移运行时，系统会将当前迁移级别保存到数据库表中。
	 * 然后将此表中的迁移级别与 $config['migration_version'] 进行比较，如果不同，则会向上迁移。此设置必须指定。
	 *
	 * @var string
	 */
	public $table = 'migrations';

	/**
	 * --------------------------------------------------------------------------
	 * Timestamp Format		时间戳格式
	 * --------------------------------------------------------------------------
	 *
	 * This is the format that will be used when creating new migrations
	 * using the CLI command:
	 *   > php spark migrate:create
	 * 这是使用 CLI 命令创建新迁移时将采用的格式：
	 *
	 * Typical formats:
	 * - YmdHis_
	 * - Y-m-d-His_
	 * - Y_m_d_His_
	 *
	 * @var string
	 */
	public $timestampFormat = 'Y-m-d-His_';
}
