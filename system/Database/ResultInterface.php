<?php
/**
 * CodeIgniter，数据库，结果接口
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

/**
 * Interface ResultInterface
 * 结果接口。
 */
interface ResultInterface
{
	/**
	 * Retrieve the results of the query. Typically an array of
	 * individual data rows, which can be either an 'array', an
	 * 'object', or a custom class name.
	 * 获取查询结果。通常是一个包含单个数据行的数组，可以是“数组”、“对象”或自定义类名。
	 *
	 * @param string $type The row type. Either 'array', 'object', or a class name to use
	 *
	 * @return array
	 */
	public function getResult(string $type = 'object'): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of custom objects.
	 * 以自定义对象数组的形式返回结果
	 *
	 * @param string $className The name of the class to use.
	 *
	 * @return mixed
	 */
	public function getCustomResultObject(string $className);

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of arrays.
	 * 将结果作为数组的数组返回。
	 *
	 * If no results, an empty array is returned.
	 * 如果没有结果，则返回一个空数组。
	 *
	 * @return array
	 */
	public function getResultArray(): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of objects.
	 * 以对象数组的形式返回结果。
	 *
	 * If no results, an empty array is returned.
	 * 如果没有结果，则返回一个空数组。
	 *
	 * @return array
	 */
	public function getResultObject(): array;

	//--------------------------------------------------------------------

	/**
	 * Wrapper object to return a row as either an array, an object, or
	 * a custom class.
	 * 包装对象，用于以数组、对象或自定义类的形式返回一行数据。
	 *
	 * If row doesn't exist, returns null.
	 * 如果row不存在，则返回null。
	 *
	 * @param mixed  $n    The index of the results to return
	 * @param string $type The type of result object. 'array', 'object' or class name.
	 *
	 * @return mixed
	 */
	public function getRow($n = 0, string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns a row as a custom class instance.
	 * 返回一行作为自定义类实例。
	 *
	 * If row doesn't exists, returns null.
	 * 如果row不存在，则返回null。
	 *
	 * @param integer $n
	 * @param string  $className
	 *
	 * @return mixed
	 */
	public function getCustomRowObject(int $n, string $className);

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an array.
	 * 将结果中的单行作为数组返回。
	 *
	 * If row doesn't exist, returns null.
	 * 如果row不存在，则返回null。
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowArray(int $n = 0);

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an object.
	 * 将结果中的单行作为对象返回。
	 *
	 * If row doesn't exist, returns null.
	 * 如果row不存在，则返回null。
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowObject(int $n = 0);

	//--------------------------------------------------------------------

	/**
	 * Assigns an item into a particular column slot.
	 * 将项分配到特定的列槽中
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @return mixed
	 */
	public function setRow($key, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the "first" row of the current results.
	 * 返回当前结果的“第一行”
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getFirstRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "last" row of the current results.
	 * 返回当前结果的“最后”行
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getLastRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "next" row of the current results.
	 * 返回当前结果的“下”行
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getNextRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "previous" row of the current results.
	 * 返回当前结果的“上一行”
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getPreviousRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns an unbuffered row and move the pointer to the next row.
	 * 返回未缓冲的行，并将指针移动到下一行。
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getUnbufferedRow(string $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Gets the number of fields in the result set.
	 * 获取结果集中字段的数目
	 *
	 * @return integer
	 */
	public function getFieldCount(): int;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 * 在结果集中生成列名数组
	 *
	 * @return array
	 */
	public function getFieldNames(): array;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 * 生成表示字段元数据的对象数组
	 *
	 * @return array
	 */
	public function getFieldData(): array;

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 * 释放当前结果
	 *
	 * @return void
	 */
	public function freeResult();

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 * 将内部指针移动到指定偏移量处。这被称为内部预取，以确保结果集从零开始。
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function dataSeek(int $n = 0);

	//--------------------------------------------------------------------
}
