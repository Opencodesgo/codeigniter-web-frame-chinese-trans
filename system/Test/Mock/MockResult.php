<?php
/**
 * CodeIgniter，测试，模拟，模拟结果
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Database\BaseResult;

class MockResult extends BaseResult
{
	/**
	 * Gets the number of fields in the result set.
	 * 获取结果集中字段的数目
	 *
	 * @return integer
	 */
	public function getFieldCount(): int
	{
		return 0;
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
		return [];
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
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 * 释放当前结果
	 *
	 * @return mixed
	 */
	public function freeResult()
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 * 将内部指针移动到指定偏移位置。这被称为内部预取，以确保结果集从零开始。
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function dataSeek($n = 0)
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 * 将结果集作为数组返回
	 *
	 * Overridden by driver classes.
	 *
	 * @return mixed
	 */
	protected function fetchAssoc()
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an object.
	 * 将结果集作为对象返回。
	 *
	 * Overridden by child classes.
	 *
	 * @param string $className
	 *
	 * @return object
	 */
	protected function fetchObject($className = 'stdClass')
	{
		return new $className;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the number of fields in the result set.
	 * 获取结果集中字段的数目
	 *
	 * @return integer
	 */
	public function getNumRows(): int
	{
		return 0;
	}

	//--------------------------------------------------------------------
}
