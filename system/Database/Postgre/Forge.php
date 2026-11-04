<?php
/**
 * CodeIgniter，数据库，Postgre，Forge
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\Forge as BaseForge;

/**
 * Forge for Postgre
 * Postgre Forge。
 */
class Forge extends BaseForge
{
	/**
	 * CHECK DATABASE EXIST statement
	 * 检查数据库存在语句
	 *
	 * @var string
	 */
	protected $checkDatabaseExistStr = 'SELECT 1 FROM pg_database WHERE datname = ?';

	/**
	 * DROP CONSTRAINT statement
	 * 删除约束语句
	 *
	 * @var string
	 */
	protected $dropConstraintStr = 'ALTER TABLE %s DROP CONSTRAINT %s';

	/**
	 * UNSIGNED support
	 * 无符号支持
	 *
	 * @var array
	 */
	protected $_unsigned = [
		'INT2'     => 'INTEGER',
		'SMALLINT' => 'INTEGER',
		'INT'      => 'BIGINT',
		'INT4'     => 'BIGINT',
		'INTEGER'  => 'BIGINT',
		'INT8'     => 'NUMERIC',
		'BIGINT'   => 'NUMERIC',
		'REAL'     => 'DOUBLE PRECISION',
		'FLOAT'    => 'DOUBLE PRECISION',
	];

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 * 在CREATE/ALTER TABLE语句中表示NULL值
	 *
	 * @var string
	 */
	protected $_null = 'NULL';

	//--------------------------------------------------------------------

	/**
	 * CREATE TABLE attributes
	 * 创建表属性
	 *
	 * @param  array $attributes Associative array of table attributes
	 * @return string
	 */
	protected function _createTableAttributes(array $attributes): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 * 更改表
	 *
	 * @param string $alterType ALTER type
	 * @param string $table     Table name
	 * @param mixed  $field     Column definition
	 *
	 * @return string|array|boolean
	 */
	protected function _alterTable(string $alterType, string $table, $field)
	{
		if (in_array($alterType, ['DROP', 'ADD'], true))
		{
			return parent::_alterTable($alterType, $table, $field);
		}

		$sql  = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);
		$sqls = [];
		foreach ($field as $data)
		{
			if ($data['_literal'] !== false)
			{
				return false;
			}

			if (version_compare($this->db->getVersion(), '8', '>=') && isset($data['type']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
						. " TYPE {$data['type']}{$data['length']}";
			}

			if (! empty($data['default']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
						. " SET DEFAULT {$data['default']}";
			}

			if (isset($data['null']))
			{
				$sqls[] = $sql . ' ALTER COLUMN ' . $this->db->escapeIdentifiers($data['name'])
						. ($data['null'] === true ? ' DROP' : ' SET') . ' NOT NULL';
			}

			if (! empty($data['new_name']))
			{
				$sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escapeIdentifiers($data['name'])
						. ' TO ' . $this->db->escapeIdentifiers($data['new_name']);
			}

			if (! empty($data['comment']))
			{
				$sqls[] = 'COMMENT ON COLUMN' . $this->db->escapeIdentifiers($table)
						. '.' . $this->db->escapeIdentifiers($data['name'])
						. " IS {$data['comment']}";
			}
		}

		return $sqls;
	}

		//--------------------------------------------------------------------

	/**
	 * Process column
	 * 进程列
	 *
	 * @param  array $field
	 * @return string
	 */
	protected function _processColumn(array $field): string
	{
		return $this->db->escapeIdentifiers($field['name'])
				. ' ' . $field['type'] . $field['length']
				. $field['default']
				. $field['null']
				. $field['auto_increment']
				. $field['unique'];
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 * 字段类型属性。
	 *
	 * Performs a data type mapping between different databases.
	 * 在不同数据库之间执行数据类型映射。
	 *
	 * @param array $attributes
	 *
	 * @return void
	 */
	protected function _attributeType(array &$attributes)
	{
		// Reset field lengths for data types that don't support it
		if (isset($attributes['CONSTRAINT']) && stripos($attributes['TYPE'], 'int') !== false)
		{
			$attributes['CONSTRAINT'] = null;
		}

		switch (strtoupper($attributes['TYPE']))
		{
			case 'TINYINT':
				$attributes['TYPE']     = 'SMALLINT';
				$attributes['UNSIGNED'] = false;
				break;
			case 'MEDIUMINT':
				$attributes['TYPE']     = 'INTEGER';
				$attributes['UNSIGNED'] = false;
				break;
			case 'DATETIME':
				$attributes['TYPE'] = 'TIMESTAMP';
				break;
			default:
				break;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 * 字段属性AUTO_INCREMENT
	 *
	 * @param array $attributes
	 * @param array $field
	 *
	 * @return void
	 */
	protected function _attributeAutoIncrement(array &$attributes, array &$field)
	{
		if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true)
		{
			$field['type'] = $field['type'] === 'NUMERIC' || $field['type'] === 'BIGINT' ? 'BIGSERIAL' : 'SERIAL';
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Drop Table
	 * 删除表。
	 *
	 * Generates a platform-specific DROP TABLE string
	 * 生成一个平台特定的DROP TABLE字符串。
	 *
	 * @param string  $table    Table name
	 * @param boolean $ifExists Whether to add an IF EXISTS condition
	 * @param boolean $cascade
	 *
	 * @return string
	 */
	protected function _dropTable(string $table, bool $ifExists, bool $cascade): string
	{
		$sql = parent::_dropTable($table, $ifExists, $cascade);

		if ($cascade === true)
		{
			$sql .= ' CASCADE';
		}

		return $sql;
	}
}
