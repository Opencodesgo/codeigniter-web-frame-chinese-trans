<?php
/**
 * CodeIgniter，数据库，Postgre，构建器
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

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Builder for Postgre
 * Postgre 构建器。
 */
class Builder extends BaseBuilder
{
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
	 * Specifies which sql statements
	 * support the ignore option.
	 * 指定哪些 SQL 语句支持 ignore 选项
	 *
	 * @var array
	 */
	protected $supportedIgnoreStatements = [
		'insert' => 'ON CONFLICT DO NOTHING',
	];

	//--------------------------------------------------------------------

	/**
	 * Compile Ignore Statement
	 * 编译忽略语句。
	 *
	 * Checks if the ignore option is supported by
	 * the Database Driver for the specific statement.
	 * 检查数据库驱动程序是否支持特定语句的忽略选项。
	 *
	 * @param string $statement
	 *
	 * @return string
	 */
	protected function compileIgnore(string $statement)
	{
		$sql = parent::compileIgnore($statement);

		if (! empty($sql))
		{
			$sql = ' ' . trim($sql);
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * ORDER BY
	 * 排序
	 *
	 * @param string  $orderBy
	 * @param string  $direction ASC, DESC or RANDOM
	 * @param boolean $escape
	 *
	 * @return BaseBuilder
	 */
	public function orderBy(string $orderBy, string $direction = '', bool $escape = null)
	{
		$direction = strtoupper(trim($direction));
		if ($direction === 'RANDOM')
		{
			if (ctype_digit($orderBy))
			{
				$orderBy = (float) ($orderBy > 1 ? "0.{$orderBy}" : $orderBy);
			}

			if (is_float($orderBy))
			{
				$this->db->simpleQuery("SET SEED {$orderBy}");
			}

			$orderBy   = $this->randomKeyword[0];
			$direction = '';
			$escape    = false;
		}

		return parent::orderBy($orderBy, $direction, $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * Increments a numeric column by the specified value.
	 * 将数值列按指定值递增
	 *
	 * @param string  $column
	 * @param integer $value
	 *
	 * @throws DatabaseException
	 *
	 * @return mixed
	 */
	public function increment(string $column, int $value = 1)
	{
		$column = $this->db->protectIdentifiers($column);

		$sql = $this->_update($this->QBFrom[0], [$column => "to_number({$column}, '9999999') + {$value}"]);

		return $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Decrements a numeric column by the specified value.
	 * 将数字列按指定值递减
	 *
	 * @param string  $column
	 * @param integer $value
	 *
	 * @throws DatabaseException
	 *
	 * @return mixed
	 */
	public function decrement(string $column, int $value = 1)
	{
		$column = $this->db->protectIdentifiers($column);

		$sql = $this->_update($this->QBFrom[0], [$column => "to_number({$column}, '9999999') - {$value}"]);

		return $this->db->query($sql, $this->binds, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Replace
	 * 替换。
	 *
	 * Compiles an replace into string and runs the query.
	 * Because PostgreSQL doesn't support the replace into command,
	 * we simply do a DELETE and an INSERT on the first key/value
	 * combo, assuming that it's either the primary key or a unique key.
	 * 将替换编译为字符串并运行查询。
	 * 由于 PostgreSQL 不支持 replace into 命令，我们只需对第一个键值对执行一次 DELETE 和 INSERT 操作，假设该键值对是主键或唯一键。
	 *
	 * @param array $set An associative array of insert values
	 *
	 * @return   mixed
	 * @throws   DatabaseException
	 * @internal param true $bool returns the generated SQL, false executes the query.
	 */
	public function replace(array $set = null)
	{
		if ($set !== null)
		{
			$this->set($set);
		}

		if (! $this->QBSet)
		{
			if (CI_DEBUG)
			{
				throw new DatabaseException('You must use the "set" method to update an entry.');
			}
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		$table = $this->QBFrom[0];

		$set = $this->binds;

		// We need to grab out the actual values from
		// the way binds are stored with escape flag.
		// 我们需要从绑定方式中提取实际值，该方式使用了转义标志进行存储。
		array_walk($set, function (&$item) {
			$item = $item[0];
		});

		$keys   = array_keys($set);
		$values = array_values($set);

		$builder = $this->db->table($table);
		$exists  = $builder->where("$keys[0] = $values[0]", null, false)->get()->getFirstRow();

		if (empty($exists))
		{
			$result = $builder->insert($set);
		}
		else
		{
			array_pop($set);
			$result = $builder->update($set, "$keys[0] = $values[0]");
		}

		unset($builder);
		$this->resetWrite();

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Delete
	 * 删除。
	 *
	 * Compiles a delete string and runs the query
	 * 编译删除字符串并运行查询。
	 *
	 * @param mixed   $where
	 * @param integer $limit
	 * @param boolean $resetData
	 *
	 * @return   mixed
	 * @throws   DatabaseException
	 * @internal param the $mixed where clause
	 * @internal param the $mixed limit clause
	 * @internal param $bool
	 */
	public function delete($where = '', int $limit = null, bool $resetData = true)
	{
		if (! empty($limit) || ! empty($this->QBLimit))
		{
			throw new DatabaseException('PostgreSQL does not allow LIMITs on DELETE queries.');
		}

		return parent::delete($where, $limit, $resetData);
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT string
	 * 限制字符串。
	 *
	 * Generates a platform-specific LIMIT clause.
	 * 生成特定于平台的LIMIT子句。
	 *
	 * @param string $sql SQL Query
	 *
	 * @return string
	 */
	protected function _limit(string $sql, bool $offsetIgnore = false): string
	{
		return $sql . ' LIMIT ' . $this->QBLimit . ($this->QBOffset ? " OFFSET {$this->QBOffset}" : '');
	}

	//--------------------------------------------------------------------

	/**
	 * Update statement
	 * 更新语句。
	 *
	 * Generates a platform-specific update string from the supplied data
	 * 从提供的数据生成特定于平台的更新字符串。
	 *
	 * @param string $table
	 * @param array  $values
	 *
	 * @return   string
	 * @throws   DatabaseException
	 * @internal param the $string table name
	 * @internal param the $array update data
	 */
	protected function _update(string $table, array $values): string
	{
		if (! empty($this->QBLimit))
		{
			throw new DatabaseException('Postgres does not support LIMITs with UPDATE queries.');
		}

		$this->QBOrderBy = [];
		return parent::_update($table, $values);
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 * Update_Batch声明。
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 * 从提供的数据生成特定于平台的批处理更新字符串。
	 *
	 * @param string $table  Table name
	 * @param array  $values Update data
	 * @param string $index  WHERE key
	 *
	 * @return string
	 */
	protected function _updateBatch(string $table, array $values, string $index): string
	{
		$ids = [];
		foreach ($values as $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field !== $index)
				{
					$final[$field][] = "WHEN {$val[$index]} THEN {$val[$field]}";
				}
			}
		}

		$cases = '';
		foreach ($final as $k => $v) // @phpstan-ignore-line
		{
			$cases .= "{$k} = (CASE {$index}\n"
					. implode("\n", $v)
					. "\nELSE {$k} END), ";
		}

		$this->where("{$index} IN(" . implode(',', $ids) . ')', null, false);

		return "UPDATE {$table} SET " . substr($cases, 0, -2) . $this->compileWhereHaving('QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * Delete statement
	 * 删除语句。
	 *
	 * Generates a platform-specific delete string from the supplied data
	 * 从提供的数据生成特定于平台的删除字符串。
	 *
	 * @param string $table The table name
	 *
	 * @return string
	 */
	protected function _delete(string $table): string
	{
		$this->QBLimit = false;
		return parent::_delete($table);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate statement
	 * 截断声明。
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 * 从提供的数据生成特定于平台的截断字符串。
	 *
	 * If the database does not support the truncate() command,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param string $table The table name
	 *
	 * @return string
	 */
	protected function _truncate(string $table): string
	{
		return 'TRUNCATE ' . $table . ' RESTART IDENTITY';
	}

	//--------------------------------------------------------------------

	/**
	 * Platform independent LIKE statement builder.
	 * 平台无关的LIKE语句构建器。
	 *
	 * In PostgreSQL, the ILIKE operator will perform case insensitive
	 * searches according to the current locale.
	 * 在 PostgreSQL 中，ILIKE 运算符将根据当前区域设置执行不区分大小写的搜索。
	 *
	 * @see https://www.postgresql.org/docs/9.2/static/functions-matching.html
	 *
	 * @param string|null  $prefix
	 * @param string       $column
	 * @param string|null  $not
	 * @param string       $bind
	 * @param boolean      $insensitiveSearch
	 *
	 * @return string     $like_statement
	 */
	public function _like_statement(?string $prefix, string $column, ?string $not, string $bind, bool $insensitiveSearch = false): string
	{
		$op = $insensitiveSearch === true ? 'ILIKE' : 'LIKE';

		return "{$prefix} {$column} {$not} {$op} :{$bind}:";
	}

	//--------------------------------------------------------------------

	/**
	 * JOIN
	 *
	 * Generates the JOIN portion of the query
	 * 生成查询的JOIN部分。
	 *
	 * @param string  $table
	 * @param string  $cond   The join condition
	 * @param string  $type   The type of join
	 * @param boolean $escape Whether not to try to escape identifiers
	 *
	 * @return BaseBuilder
	 */
	public function join(string $table, string $cond, string $type = '', bool $escape = null)
	{
		if (! in_array('FULL OUTER', $this->joinTypes, true))
		{
			$this->joinTypes = array_merge($this->joinTypes, ['FULL OUTER']);
		}

		return parent::join($table, $cond, $type, $escape);
	}

	//--------------------------------------------------------------------

}
