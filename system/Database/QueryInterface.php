<?php
/**
 * CodeIgniter，数据库，查询接口
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
 * Interface QueryInterface
 * 查询接口。
 *
 * Represents a single statement that can be executed against the database.
 * Statements are platform-specific and can handle binding of binds.
 * 表示可以对数据库执行的单个语句。
 * 语句是特定于平台的，可以处理绑定的绑定。
 */
interface QueryInterface
{
	/**
	 * Sets the raw query string to use for this statement.
	 * 设置要用于此语句的原始查询字符串
	 *
	 * @param string  $sql
	 * @param mixed   $binds
	 * @param boolean $setEscape
	 *
	 * @return mixed
	 */
	public function setQuery(string $sql, $binds = null, bool $setEscape = true);

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 * 返回绑定操作完成后最终处理的查询字符串
	 *
	 * @return mixed
	 */
	public function getQuery();

	//--------------------------------------------------------------------

	/**
	 * Records the execution time of the statement using microtime(true)
	 * for it's start and end values. If no end value is present, will
	 * use the current time to determine total duration.
	 * 使用 microtime(true) 记录语句的执行时间，分别记录开始和结束值。
	 * 如果未提供结束值，则使用当前时间来确定总时长。
	 *
	 * @param float $start
	 * @param float $end
	 *
	 * @return mixed
	 */
	public function setDuration(float $start, float $end = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 * 返回此查询执行期间的持续时间，如果查询尚未执行则返回 null。
	 *
	 * @param integer $decimals The accuracy of the returned time.
	 *
	 * @return string
	 */
	public function getDuration(int $decimals = 6): string;

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
	 * 存储针对此查询发生的错误描述
	 *
	 * @param integer $code
	 * @param string  $error
	 */
	public function setError(int $code, string $error);

	//--------------------------------------------------------------------

	/**
	 * Reports whether this statement created an error not.
	 * 报告此语句是否创建了错误
	 *
	 * @return boolean
	 */
	public function hasError(): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 * 返回在执行此语句时创建的错误代码
	 *
	 * @return integer
	 */
	public function getErrorCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 * 返回在执行此语句时创建的错误消息
	 *
	 * @return string
	 */
	public function getErrorMessage(): string;

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 * 确定语句是否是写类型查询
	 *
	 * @return boolean
	 */
	public function isWriteType(): bool;

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 * 将一个表前缀替换为一个新前缀
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return mixed
	 */
	public function swapPrefix(string $orig, string $swap);

	//--------------------------------------------------------------------
}
