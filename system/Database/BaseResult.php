<?php
/**
 * CodeIgniter，数据库，基本结果
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

use CodeIgniter\Entity;

/**
 * Class BaseResult
 * 类基本结果。
 */
abstract class BaseResult implements ResultInterface
{
	/**
	 * Connection ID
	 * 连接ID
	 *
	 * @var resource|object
	 */
	public $connID;

	/**
	 * Result ID
	 * 结果ID
	 *
	 * @var resource|object|boolean
	 */
	public $resultID;

	/**
	 * Result Array
	 * 结果数组
	 *
	 * @var array[]
	 */
	public $resultArray = [];

	/**
	 * Result Object
	 * 结果对象
	 *
	 * @var object[]
	 */
	public $resultObject = [];

	/**
	 * Custom Result Object
	 * 自定义结果对象
	 *
	 * @var array
	 */
	public $customResultObject = [];

	/**
	 * Current Row index
	 * 当前行索引
	 *
	 * @var integer
	 */
	public $currentRow = 0;

	/**
	 * The number of records in the query result
	 * 查询结果中的记录条数
	 *
	 * @var integer|null
	 */
	protected $numRows = null;

	/**
	 * Row data
	 * 行数据
	 *
	 * @var array|null
	 */
	public $rowData;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param object|resource $connID
	 * @param object|resource $resultID
	 */
	public function __construct(&$connID, &$resultID)
	{
		$this->connID   = $connID;
		$this->resultID = $resultID;
	}

	//--------------------------------------------------------------------

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
	public function getResult(string $type = 'object'): array
	{
		if ($type === 'array')
		{
			return $this->getResultArray();
		}

		if ($type === 'object')
		{
			return $this->getResultObject();
		}

		return $this->getCustomResultObject($type);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of custom objects.
	 * 以自定义对象数组的形式返回结果
	 *
	 * @param string $className The name of the class to use.
	 *
	 * @return mixed
	 */
	public function getCustomResultObject(string $className)
	{
		if (isset($this->customResultObject[$className]))
		{
			return $this->customResultObject[$className];
		}

		if (is_bool($this->resultID) || ! $this->resultID)
		{
			return [];
		}

		// Don't fetch the result set again if we already have it
		// 如果我们已经有结果集，就不要再获取它了。
		$_data = null;
		if (($c = count($this->resultArray)) > 0)
		{
			$_data = 'resultArray';
		}
		elseif (($c = count($this->resultObject)) > 0)
		{
			$_data = 'resultObject';
		}

		if ($_data !== null)
		{
			for ($i = 0; $i < $c; $i ++)
			{
				$this->customResultObject[$className][$i] = new $className();

				foreach ($this->{$_data}[$i] as $key => $value)
				{
					$this->customResultObject[$className][$i]->$key = $value;
				}
			}

			return $this->customResultObject[$className];
		}

		is_null($this->rowData) || $this->dataSeek();
		$this->customResultObject[$className] = [];

		while ($row = $this->fetchObject($className))
		{
			if (! is_subclass_of($row, Entity::class) && method_exists($row, 'syncOriginal'))
			{
				$row->syncOriginal();
			}

			$this->customResultObject[$className][] = $row;
		}

		// @phpstan-ignore-next-line
		return $this->customResultObject[$className];
	}

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
	public function getResultArray(): array
	{
		if (! empty($this->resultArray))
		{
			return $this->resultArray;
		}

		// In the event that query caching is on, the result_id variable
		// will not be a valid resource so we'll simply return an empty
		// array.
		// 如果启用了查询缓存，result_id 变量将不是一个有效的资源，因此我们只需返回一个空数组。
		if (is_bool($this->resultID) || ! $this->resultID)
		{
			return [];
		}

		if ($this->resultObject)
		{
			foreach ($this->resultObject as $row)
			{
				$this->resultArray[] = (array) $row;
			}

			return $this->resultArray;
		}

		is_null($this->rowData) || $this->dataSeek();
		while ($row = $this->fetchAssoc())
		{
			$this->resultArray[] = $row;
		}

		return $this->resultArray;
	}

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
	public function getResultObject(): array
	{
		if (! empty($this->resultObject))
		{
			return $this->resultObject;
		}

		// In the event that query caching is on, the result_id variable
		// will not be a valid resource so we'll simply return an empty
		// array.
		// 如果启用了查询缓存，result_id 变量将不是一个有效的资源，因此我们只需返回一个空数组。
		if (is_bool($this->resultID) || ! $this->resultID)
		{
			return [];
		}

		if ($this->resultArray)
		{
			foreach ($this->resultArray as $row)
			{
				$this->resultObject[] = (object) $row;
			}

			return $this->resultObject;
		}

		is_null($this->rowData) || $this->dataSeek();
		while ($row = $this->fetchObject())
		{
			if (! is_subclass_of($row, Entity::class) && method_exists($row, 'syncOriginal'))
			{
				$row->syncOriginal();
			}

			$this->resultObject[] = $row;
		}

		// @phpstan-ignore-next-line
		return $this->resultObject;
	}

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
	public function getRow($n = 0, string $type = 'object')
	{
		if (! is_numeric($n))
		{
			// We cache the row data for subsequent uses
			// 我们缓存行数据以供后续使用
			is_array($this->rowData) || $this->rowData = $this->getRowArray();

			// array_key_exists() instead of isset() to allow for NULL values
			if (empty($this->rowData) || ! array_key_exists($n, $this->rowData))
			{
				return null;
			}

			return $this->rowData[$n];
		}

		if ($type === 'object')
		{
			return $this->getRowObject($n);
		}

		if ($type === 'array')
		{
			return $this->getRowArray($n);
		}

		return $this->getCustomRowObject($n, $type);
	}

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
	public function getCustomRowObject(int $n, string $className)
	{
		isset($this->customResultObject[$className]) || $this->getCustomResultObject($className);

		if (empty($this->customResultObject[$className]))
		{
			return null;
		}

		if ($n !== $this->currentRow && isset($this->customResultObject[$className][$n]))
		{
			$this->currentRow = $n;
		}

		return $this->customResultObject[$className][$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an array.
	 * 将结果中的单行作为数组返回。
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowArray(int $n = 0)
	{
		$result = $this->getResultArray();
		if (empty($result))
		{
			return null;
		}

		if ($n !== $this->currentRow && isset($result[$n]))
		{
			$this->currentRow = $n;
		}

		return $result[$this->currentRow];
	}

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
	public function getRowObject(int $n = 0)
	{
		$result = $this->getResultObject();
		if (empty($result))
		{
			return null;
		}

		if ($n !== $this->customResultObject && isset($result[$n]))
		{
			$this->currentRow = $n;
		}

		return $result[$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Assigns an item into a particular column slot.
	 * 将项分配到特定的列槽中
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function setRow($key, $value = null)
	{
		// We cache the row data for subsequent uses
		// 我们缓存随后使用的行数据
		if (! is_array($this->rowData))
		{
			$this->rowData = $this->getRowArray();
		}

		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->rowData[$k] = $v;
			}

			return;
		}

		if ($key !== '' && $value !== null)
		{
			$this->rowData[$key] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "first" row of the current results.
	 * 返回当前结果的“第一行”
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getFirstRow(string $type = 'object')
	{
		$result = $this->getResult($type);

		return (empty($result)) ? null : $result[0];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "last" row of the current results.
	 * 返回当前结果的“最后”行
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getLastRow(string $type = 'object')
	{
		$result = $this->getResult($type);

		return (empty($result)) ? null : $result[count($result) - 1];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "next" row of the current results.
	 * 返回当前结果的“下”行
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getNextRow(string $type = 'object')
	{
		$result = $this->getResult($type);
		if (empty($result))
		{
			return null;
		}

		return isset($result[$this->currentRow + 1]) ? $result[++ $this->currentRow] : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "previous" row of the current results.
	 * 返回当前结果的“上一行”
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getPreviousRow(string $type = 'object')
	{
		$result = $this->getResult($type);
		if (empty($result))
		{
			return null;
		}

		if (isset($result[$this->currentRow - 1]))
		{
			-- $this->currentRow;
		}

		return $result[$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an unbuffered row and move the pointer to the next row.
	 * 返回未缓冲的行，并将指针移动到下一行。
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getUnbufferedRow(string $type = 'object')
	{
		if ($type === 'array')
		{
			return $this->fetchAssoc();
		}

		if ($type === 'object')
		{
			return $this->fetchObject();
		}

		return $this->fetchObject($type);
	}

	//--------------------------------------------------------------------

	/**
	 * Number of rows in the result set; checks for previous count, falls
	 * back on counting resultArray or resultObject, finally fetching resultArray
	 * if nothing was previously fetched
	 * 结果集中的行数；会检查之前的计数，如果之前没有获取到，则回退到统计 resultArray 或 resultObject，
	 * 最后如果之前未获取任何内容，则返回 resultArray。
	 *
	 * @return integer
	 */
	public function getNumRows(): int
	{
		if (is_int($this->numRows))
		{
			return $this->numRows;
		}
		if ($this->resultArray !== [])
		{
			return $this->numRows = count($this->resultArray);
		}
		if ($this->resultObject !== [])
		{
			return $this->numRows = count($this->resultObject);
		}

		return $this->numRows = count($this->getResultArray());
	}

	/**
	 * Gets the number of fields in the result set.
	 * 获取结果集中字段的数目
	 *
	 * @return integer
	 */
	abstract public function getFieldCount(): int;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 * 在结果集中生成列名数组
	 *
	 * @return array
	 */
	abstract public function getFieldNames(): array;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 * 生成表示字段元数据的对象数组
	 *
	 * @return array
	 */
	abstract public function getFieldData(): array;

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 * 释放当前结果
	 *
	 * @return void
	 */
	abstract public function freeResult();

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
	abstract public function dataSeek(int $n = 0);

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 * 将结果集作为数组返回。
	 *
	 * Overridden by driver classes.
	 *
	 * @return mixed
	 */
	abstract protected function fetchAssoc();

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
	 * @return object
	 */
	abstract protected function fetchObject(string $className = 'stdClass');

	//--------------------------------------------------------------------
}
