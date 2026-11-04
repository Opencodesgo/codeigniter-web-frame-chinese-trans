<?php
/**
 * CodeIgniter，数据库，异常，数据异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use RuntimeException;

class DataException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Used by the Model's trigger() method when the callback cannot be found.
	 * 当找不到回调函数时，由Model的trigger()方法使用。
	 *
	 * @param string $method
	 *
	 * @return DataException
	 */
	public static function forInvalidMethodTriggered(string $method)
	{
		return new static(lang('Database.invalidEvent', [$method]));
	}

	/**
	 * Used by Model's insert/update methods when there isn't
	 * any data to actually work with.
	 * 当没有实际数据可处理时，模型的插入/更新方法会使用此功能。
	 *
	 * @param string $mode
	 *
	 * @return DataException
	 */
	public static function forEmptyDataset(string $mode)
	{
		return new static(lang('Database.emptyDataset', [$mode]));
	}

	/**
	 * Used by Model's insert/update methods when there is no
	 * primary key defined and Model has option `useAutoIncrement`
	 * set to false.
	 * 当模型未定义主键且模型的 `useAutoIncrement` 选项设置为 false 时，由模型的插入/更新方法使用。
	 *
	 * @param string $mode
	 *
	 * @return DataException
	 */
	public static function forEmptyPrimaryKey(string $mode)
	{
		return new static(lang('Database.emptyPrimaryKey', [$mode]));
	}

	/**
	 * Thrown when an argument for one of the Model's methods
	 * were empty or otherwise invalid, and they could not be
	 * to work correctly for that method.
	 * 当模型方法的参数为空或无效时抛出，导致该方法无法正常工作。
	 *
	 * @param string $argument
	 *
	 * @return DataException
	 */
	public static function forInvalidArgument(string $argument)
	{
		return new static(lang('Database.invalidArgument', [$argument]));
	}

	public static function forInvalidAllowedFields(string $model)
	{
		return new static(lang('Database.invalidAllowedFields', [$model]));
	}

	public static function forTableNotFound(string $table)
	{
		return new static(lang('Database.tableNotFound', [$table]));
	}

	public static function forEmptyInputGiven(string $argument)
	{
		return new static(lang('Database.forEmptyInputGiven', [$argument]));
	}

	public static function forFindColumnHaveMultipleColumns()
	{
		return new static(lang('Database.forFindColumnHaveMultipleColumns'));
	}
}
