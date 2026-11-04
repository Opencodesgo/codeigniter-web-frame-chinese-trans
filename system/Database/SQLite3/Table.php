<?php
/**
 * CodeIgniter，数据库，SQLite3，表
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

use CodeIgniter\Database\Exceptions\DataException;

/**
 * Class Table
 * 表类。
 *
 * Provides missing features for altering tables that are common
 * in other supported databases, but are missing from SQLite.
 * These are needed in order to support migrations during testing
 * when another database is used as the primary engine, but
 * SQLite in memory databases are used for faster test execution.
 * 提供了一些其他支持的数据库中常见的、但在 SQLite 中缺失的用于修改表的功能。
 */
class Table
{
	/**
	 * All of the fields this table represents.
	 * 该表表示的所有字段
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * All of the unique/primary keys in the table.
	 * 表中所有的唯一/主键
	 *
	 * @var array
	 */
	protected $keys = [];

	/**
	 * All of the foreign keys in the table.
	 * 表中的所有外键
	 *
	 * @var array
	 */
	protected $foreignKeys = [];

	/**
	 * The name of the table we're working with.
	 * 我们正在处理的表的名称
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * The name of the table, with database prefix
	 * 表的名称，带有数据库前缀。
	 *
	 * @var string
	 */
	protected $prefixedTableName;

	/**
	 * Database connection.
	 * 数据库连接
	 *
	 * @var Connection
	 */
	protected $db;

	/**
	 * Handle to our forge.
	 * 我们的forge手柄
	 *
	 * @var Forge
	 */
	protected $forge;

	/**
	 * Table constructor.
	 * 表构造器
	 *
	 * @param Connection $db
	 * @param Forge      $forge
	 */
	public function __construct(Connection $db, Forge $forge)
	{
		$this->db    = $db;
		$this->forge = $forge;
	}

	/**
	 * Reads an existing database table and
	 * collects all of the information needed to
	 * recreate this table.
	 * 读取现有的数据库表，并收集重建该表所需的所有信息。
	 *
	 * @param string $table
	 *
	 * @return Table
	 */
	public function fromTable(string $table)
	{
		$this->prefixedTableName = $table;

		// Remove the prefix, if any, since it's
		// already been added by the time we get here...
		// 如果已添加前缀，就将其移除，因为到此时它已经存在了……
		$prefix = $this->db->DBPrefix; // @phpstan-ignore-line
		if (! empty($prefix))
		{
			if (strpos($table, $prefix) === 0)
			{
				$table = substr($table, strlen($prefix));
			}
		}

		if (! $this->db->tableExists($this->prefixedTableName))
		{
			throw DataException::forTableNotFound($this->prefixedTableName);
		}

		$this->tableName = $table;

		$this->fields = $this->formatFields($this->db->getFieldData($table));

		$this->keys = array_merge($this->keys, $this->formatKeys($this->db->getIndexData($table)));

		$this->foreignKeys = $this->db->getForeignKeyData($table);

		return $this;
	}

	/**
	 * Called after `fromTable` and any actions, like `dropColumn`, etc,
	 * to finalize the action. It creates a temp table, creates the new
	 * table with modifications, and copies the data over to the new table.
	 * Resets the connection dataCache to be sure changes are collected.
	 *
	 * @return boolean
	 */
	public function run(): bool
	{
		$this->db->query('PRAGMA foreign_keys = OFF');

		$this->db->transStart();

		$this->forge->renameTable($this->tableName, "temp_{$this->tableName}");

		$this->forge->reset();

		$this->createTable();

		$this->copyData();

		$this->forge->dropTable("temp_{$this->tableName}");

		$success = $this->db->transComplete();

		$this->db->query('PRAGMA foreign_keys = ON');

		$this->db->resetDataCache();

		return $success;
	}

	/**
	 * Drops columns from the table.
	 * 从表中删除列
	 *
	 * @param string|array $columns
	 *
	 * @return Table
	 */
	public function dropColumn($columns)
	{
		//unset($this->fields[$column]);

		if (is_string($columns))
		{
			$columns = explode(',', $columns);
		}

		foreach ($columns as $column)
		{
			$column = trim($column);
			if (isset($this->fields[$column]))
			{
				unset($this->fields[$column]);
			}
		}

		return $this;
	}

	/**
	 * Modifies a field, including changing data type,
	 * renaming, etc.
	 * 修改字段，包括更改数据类型、重命名等。
	 *
	 * @param array $field
	 *
	 * @return Table
	 */
	public function modifyColumn(array $field)
	{
		$field = $field[0];

		$oldName = $field['name'];
		unset($field['name']);

		$this->fields[$oldName] = $field;

		return $this;
	}

	/**
	 * Drops a foreign key from this table so that
	 * it won't be recreated in the future.
	 * 从该表中删除外键，以防止将来重新创建。
	 *
	 * @param string $column
	 *
	 * @return Table
	 */
	public function dropForeignKey(string $column)
	{
		if (empty($this->foreignKeys))
		{
			return $this;
		}

		for ($i = 0; $i < count($this->foreignKeys); $i++)
		{
			if ($this->foreignKeys[$i]->table_name !== $this->tableName)
			{
				continue;
			}

			// The column name should be the first thing in the constraint name
			if (strpos($this->foreignKeys[$i]->constraint_name, $column) !== 0)
			{
				continue;
			}

			unset($this->foreignKeys[$i]);
		}

		return $this;
	}

	/**
	 * Creates the new table based on our current fields.
	 * 基于当前字段创建新表
	 *
	 * @return mixed
	 */
	protected function createTable()
	{
		$this->dropIndexes();
		$this->db->resetDataCache();

		// Handle any modified columns.
		$fields = [];
		foreach ($this->fields as $name => $field)
		{
			if (isset($field['new_name']))
			{
				$fields[$field['new_name']] = $field;
				continue;
			}

			$fields[$name] = $field;
		}

		$this->forge->addField($fields);

		// Unique/Index keys
		if (is_array($this->keys))
		{
			foreach ($this->keys as $key)
			{
				switch ($key['type'])
				{
					case 'primary':
						$this->forge->addPrimaryKey($key['fields']);
						break;
					case 'unique':
						$this->forge->addUniqueKey($key['fields']);
						break;
					case 'index':
						$this->forge->addKey($key['fields']);
						break;
				}
			}
		}

		// Foreign Keys

		return $this->forge->createTable($this->tableName);
	}

	/**
	 * Copies data from our old table to the new one,
	 * taking care map data correctly based on any columns
	 * that have been renamed.
	 * 将数据从旧表复制到新表，并根据已重命名的列正确处理地图数据。
	 *
	 * @return void
	 */
	protected function copyData()
	{
		$exFields  = [];
		$newFields = [];

		foreach ($this->fields as $name => $details)
		{
			// Are we modifying the column?
			if (isset($details['new_name']))
			{
				$newFields[] = $details['new_name'];
			}
			else
			{
				$newFields[] = $name;
			}

			$exFields[] = $name;
		}

		$exFields  = implode(', ', $exFields);
		$newFields = implode(', ', $newFields);

		// @phpstan-ignore-next-line
		$this->db->query("INSERT INTO {$this->prefixedTableName}({$newFields}) SELECT {$exFields} FROM {$this->db->DBPrefix}temp_{$this->tableName}");
	}

	/**
	 * Converts fields retrieved from the database to
	 * the format needed for creating fields with Forge.
	 * 将从数据库中获取的字段转换为创建 Forge 字段所需的格式。
	 *
	 * @param array|boolean $fields
	 *
	 * @return mixed
	 */
	protected function formatFields($fields)
	{
		if (! is_array($fields))
		{
			return $fields;
		}

		$return = [];

		foreach ($fields as $field)
		{
			$return[$field->name] = [
				'type'     => $field->type,
				'default'  => $field->default,
				'nullable' => $field->nullable,
			];

			if ($field->primary_key)
			{
				$this->keys[$field->name] = [
					'fields' => [$field->name],
					'type'   => 'primary',
				];
			}
		}

		return $return;
	}

	/**
	 * Converts keys retrieved from the database to
	 * the format needed to create later.
	 * 将从数据库中获取的密钥转换为后续创建所需的格式
	 *
	 * @param mixed $keys
	 *
	 * @return mixed
	 */
	protected function formatKeys($keys)
	{
		if (! is_array($keys))
		{
			return $keys;
		}

		$return = [];

		foreach ($keys as $name => $key)
		{
			$return[$name] = [
				'fields' => $key->fields,
				'type'   => 'index',
			];
		}

		return $return;
	}

	/**
	 * Attempts to drop all indexes and constraints
	 * from the database for this table.
	 * 尝试删除此表的数据库中的所有索引和约束
	 *
	 * @return null|void
	 */
	protected function dropIndexes()
	{
		if (! is_array($this->keys) || $this->keys === [])
		{
			return;
		}

		foreach ($this->keys as $name => $key)
		{
			if ($key['type'] === 'primary' || $key['type'] === 'unique')
			{
				continue;
			}

			$this->db->query("DROP INDEX IF EXISTS '{$name}'");
		}
	}
}
