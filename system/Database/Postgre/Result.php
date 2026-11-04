<?php
/**
 * CodeIgniter，数据库，Postgre，结果
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

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Entity;
use stdClass;

/**
 * Result for Postgre
 * Postgre 结果。
 */
class Result extends BaseResult
{
	/**
	 * Gets the number of fields in the result set.
	 * 获取结果集中字段的数目
	 *
	 * @return integer
	 */
	public function getFieldCount(): int
	{
		return pg_num_fields($this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 * 在结果集中生成列名数组
	 *
	 * @return array
	 */
	public function getFieldNames(): array
	{
		$fieldNames = [];
		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i ++)
		{
			$fieldNames[] = pg_field_name($this->resultID, $i);
		}

		return $fieldNames;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 * 生成表示字段元数据的对象数组
	 *
	 * @return array
	 */
	public function getFieldData(): array
	{
		$retVal = [];

		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i ++)
		{
			$retVal[$i]             = new stdClass();
			$retVal[$i]->name       = pg_field_name($this->resultID, $i);
			$retVal[$i]->type       = pg_field_type_oid($this->resultID, $i);
			$retVal[$i]->type_name  = pg_field_type($this->resultID, $i);
			$retVal[$i]->max_length = pg_field_size($this->resultID, $i);
			$retVal[$i]->length     = $retVal[$i]->max_length;
			// $retVal[$i]->primary_key = (int)($fieldData[$i]->flags & 2);
			// $retVal[$i]->default     = $fieldData[$i]->def;
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 * 释放当前结果
	 *
	 * @return void
	 */
	public function freeResult()
	{
		if (is_resource($this->resultID))
		{
			pg_free_result($this->resultID);
			$this->resultID = false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 * 将内部指针移动到指定偏移量处。
	 * 这被称为在获取结果前的内部处理，以确保结果集从零开始。
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function dataSeek(int $n = 0)
	{
		return pg_result_seek($this->resultID, $n);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 * 将结果集作为数组返回。
	 *
	 * Overridden by driver classes.
	 * 被驱动程序类覆盖。
	 *
	 * @return mixed
	 */
	protected function fetchAssoc()
	{
		return pg_fetch_assoc($this->resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an object.
	 * 将结果集作为对象返回。
	 *
	 * Overridden by child classes.
	 * 被子类覆盖。
	 *
	 * @param string $className
	 *
	 * @return object|boolean|Entity
	 */
	protected function fetchObject(string $className = 'stdClass')
	{
		if (is_subclass_of($className, Entity::class))
		{
			return empty($data = $this->fetchAssoc()) ? false : (new $className())->setAttributes($data);
		}
		return pg_fetch_object($this->resultID, null, $className);
	}

	//--------------------------------------------------------------------
	/**
	 * Returns the number of rows in the resultID (i.e., PostgreSQL query result resource)
	 * 返回resultID中的行数
	 *
	 * @return integer The number of rows in the query result
	 */
	public function getNumRows(): int
	{
		if (! is_int($this->numRows))
		{
			$this->numRows = pg_num_rows($this->resultID);
		}

		return $this->numRows;
	}

	//--------------------------------------------------------------------
}
