<?php
/**
 * CodeIgniter，数据库，查询
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
 * Query builder
 * 查询构建器。
 */
class Query implements QueryInterface
{
	/**
	 * The query string, as provided by the user.
	 * 查询字符串，由用户提供。
	 *
	 * @var string
	 */
	protected $originalQueryString;

	/**
	 * The final query string after binding, etc.
	 * 绑定后的最终查询字符串等
	 *
	 * @var string
	 */
	protected $finalQueryString;

	/**
	 * The binds and their values used for binding.
	 * 绑定及其用于绑定的值
	 *
	 * @var array
	 */
	protected $binds = [];

	/**
	 * Bind marker
	 * 绑定标记。
	 *
	 * Character used to identify values in a prepared statement.
	 * 用于在预处理语句中标识值的字符。
	 *
	 * @var string
	 */
	protected $bindMarker = '?';

	/**
	 * The start time in seconds with microseconds
	 * for when this query was executed.
	 * 此查询执行时的开始时间（以秒为单位，含微秒）
	 *
	 * @var string|float
	 */
	protected $startTime;

	/**
	 * The end time in seconds with microseconds
	 * for when this query was executed.
	 * 此查询执行时的结束时间（以秒为单位，含微秒）。
	 *
	 * @var float
	 */
	protected $endTime;

	/**
	 * The error code, if any.
	 * 错误码（如果有的话）
	 *
	 * @var integer
	 */
	protected $errorCode;

	/**
	 * The error message, if any.
	 * 错误消息（如果有的话）
	 *
	 * @var string
	 */
	protected $errorString;

	/**
	 * Pointer to database connection.
	 * Mainly for escaping features.
	 * 指向数据库连接的指针。主要用于转义功能。
	 *
	 * @var ConnectionInterface
	 */
	public $db;

	//--------------------------------------------------------------------

	/**
	 * BaseQuery constructor.
	 * BaseQuery构造函数
	 *
	 * @param ConnectionInterface $db
	 */
	public function __construct(ConnectionInterface &$db)
	{
		$this->db = $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the raw query string to use for this statement.
	 * 设置要用于此语句的原始查询字符串
	 *
	 * @param string  $sql
	 * @param mixed   $binds
	 * @param boolean $setEscape
	 *
	 * @return $this
	 */
	public function setQuery(string $sql, $binds = null, bool $setEscape = true)
	{
		$this->originalQueryString = $sql;

		if (! is_null($binds))
		{
			if (! is_array($binds))
			{
				$binds = [$binds];
			}

			if ($setEscape)
			{
				array_walk($binds, function (&$item) {
					$item = [
						$item,
						true,
					];
				});
			}
			$this->binds = $binds;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Will store the variables to bind into the query later.
	 * 将存储稍后绑定到查询中的变量
	 *
	 * @param array   $binds
	 * @param boolean $setEscape
	 *
	 * @return $this
	 */
	public function setBinds(array $binds, bool $setEscape = true)
	{
		if ($setEscape)
		{
			array_walk($binds, function (&$item) {
				$item = [
					$item,
					true,
				];
			});
		}

		$this->binds = $binds;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 * 返回绑定操作完成后最终处理的查询字符串
	 *
	 * @return string
	 */
	public function getQuery(): string
	{
		if (empty($this->finalQueryString))
		{
			$this->finalQueryString = $this->originalQueryString;
		}

		$this->compileBinds();

		return $this->finalQueryString;
	}

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
	 * @return $this
	 */
	public function setDuration(float $start, float $end = null)
	{
		$this->startTime = $start;

		if (is_null($end))
		{
			$end = microtime(true);
		}

		$this->endTime = $end;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the start time in seconds with microseconds.
	 * 以微秒为单位返回起始时间
	 *
	 * @param boolean $returnRaw
	 * @param integer $decimals
	 *
	 * @return string|float
	 */
	public function getStartTime(bool $returnRaw = false, int $decimals = 6)
	{
		if ($returnRaw)
		{
			return $this->startTime;
		}

		return number_format($this->startTime, $decimals);
	}

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
	public function getDuration(int $decimals = 6): string
	{
		return number_format(($this->endTime - $this->startTime), $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
	 * 存储针对此查询发生的错误描述
	 *
	 * @param integer $code
	 * @param string  $error
	 *
	 * @return $this
	 */
	public function setError(int $code, string $error)
	{
		$this->errorCode   = $code;
		$this->errorString = $error;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Reports whether this statement created an error not.
	 * 报告此语句是否创建了错误
	 *
	 * @return boolean
	 */
	public function hasError(): bool
	{
		return ! empty($this->errorString);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 * 返回在执行此语句时创建的错误代码
	 *
	 * @return integer
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 * 返回在执行此语句时创建的错误消息
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		return $this->errorString;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 * 确定语句是否是写类型查询
	 *
	 * @return boolean
	 */
	public function isWriteType(): bool
	{
		return (bool) preg_match(
						'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i', $this->originalQueryString);
	}

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 * 将一个表前缀替换为一个新前缀
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return $this
	 */
	public function swapPrefix(string $orig, string $swap)
	{
		$sql = empty($this->finalQueryString) ? $this->originalQueryString : $this->finalQueryString;

		$this->finalQueryString = preg_replace('/(\W)' . $orig . '(\S+?)/', '\\1' . $swap . '\\2', $sql);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the original SQL that was passed into the system.
	 * 返回传递到系统中的原始SQL
	 *
	 * @return string
	 */
	public function getOriginalQuery(): string
	{
		return $this->originalQueryString;
	}

	//--------------------------------------------------------------------

	/**
	 * Escapes and inserts any binds into the finalQueryString object.
	 * 转义并插入任何绑定到finalQueryString对象中
	 *
	 * @return null|void
	 */
	protected function compileBinds()
	{
		$sql = $this->finalQueryString;

		$hasNamedBinds = strpos($sql, ':') !== false && strpos($sql, ':=') === false;

		if (empty($this->binds)
			|| empty($this->bindMarker)
			|| (strpos($sql, $this->bindMarker) === false
			&& $hasNamedBinds === false)
		)
		{
			return;
		}

		if (! is_array($this->binds))
		{
			$binds     = [$this->binds];
			$bindCount = 1;
		}
		else
		{
			$binds     = $this->binds;
			$bindCount = count($binds);
		}

		// Reverse the binds so that duplicate named binds
		// will be processed prior to the original binds.
		// 反转绑定顺序，使重复的命名绑定在原始绑定之前被处理。
		if (! is_numeric(key(array_slice($binds, 0, 1))))
		{
			$binds = array_reverse($binds);
		}

		// We'll need marker length later
		$ml = strlen($this->bindMarker);

		if ($hasNamedBinds)
		{
			$sql = $this->matchNamedBinds($sql, $binds);
		}
		else
		{
			$sql = $this->matchSimpleBinds($sql, $binds, $bindCount, $ml);
		}

		$this->finalQueryString = $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Match bindings
	 * 匹配的绑定
	 *
	 * @param  string $sql
	 * @param  array  $binds
	 * @return string
	 */
	protected function matchNamedBinds(string $sql, array $binds): string
	{
		$replacers = [];

		foreach ($binds as $placeholder => $value)
		{
			// $value[1] contains the boolean whether should be escaped or not
			// $value[1]包含是否应该转义的布尔值
			$escapedValue = $value[1] ? $this->db->escape($value[0]) : $value[0];

			// In order to correctly handle backlashes in saved strings
			// we will need to preg_quote, so remove the wrapping escape characters
			// otherwise it will get escaped.
			if (is_array($value[0]))
			{
				$escapedValue = '(' . implode(',', $escapedValue) . ')';
			}

			$replacers[":{$placeholder}:"] = $escapedValue;
		}

		return strtr($sql, $replacers);
	}

	//--------------------------------------------------------------------

	/**
	 * Match bindings
	 * 匹配绑定
	 *
	 * @param  string  $sql
	 * @param  array   $binds
	 * @param  integer $bindCount
	 * @param  integer $ml
	 * @return string
	 */
	protected function matchSimpleBinds(string $sql, array $binds, int $bindCount, int $ml): string
	{
		// Make sure not to replace a chunk inside a string that happens to match the bind marker
		// 确保不要替换恰好与绑定标记匹配的字符串中的块
		if ($c = preg_match_all("/'[^']*'/i", $sql, $matches))
		{
			$c = preg_match_all('/' . preg_quote($this->bindMarker, '/') . '/i', str_replace($matches[0], str_replace($this->bindMarker, str_repeat(' ', $ml), $matches[0]), $sql, $c), $matches, PREG_OFFSET_CAPTURE);

			// Bind values' count must match the count of markers in the query
			if ($bindCount !== $c)
			{
				return $sql;
			}
		}
		// Number of binds must match bindMarkers in the string.
		// 绑定的数量必须匹配字符串中的bindmarker
		elseif (($c = preg_match_all('/' . preg_quote($this->bindMarker, '/') . '/i', $sql, $matches, PREG_OFFSET_CAPTURE)) !== $bindCount)
		{
			return $sql;
		}

		do
		{
			$c--;
			$escapedValue = $binds[$c][1] ? $this->db->escape($binds[$c][0]) : $binds[$c][0];
			if (is_array($escapedValue))
			{
				$escapedValue = '(' . implode(',', $escapedValue) . ')';
			}
			$sql = substr_replace($sql, $escapedValue, $matches[0][$c][1], $ml);
		}
		while ($c !== 0);

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns string to display in debug toolbar
	 * 返回要在调试工具栏中显示的字符串
	 *
	 * @return string
	 */
	public function debugToolbarDisplay(): string
	{
		// Key words we want bolded
		static $highlight = [
			'SELECT',
			'DISTINCT',
			'FROM',
			'WHERE',
			'AND',
			'LEFT&nbsp;JOIN',
			'RIGHT&nbsp;JOIN',
			'JOIN',
			'ORDER&nbsp;BY',
			'GROUP&nbsp;BY',
			'LIMIT',
			'INSERT',
			'INTO',
			'VALUES',
			'UPDATE',
			'OR&nbsp;',
			'HAVING',
			'OFFSET',
			'NOT&nbsp;IN',
			'IN',
			'LIKE',
			'NOT&nbsp;LIKE',
			'COUNT',
			'MAX',
			'MIN',
			'ON',
			'AS',
			'AVG',
			'SUM',
			'(',
			')',
		];

		$sql = $this->getQuery();

		foreach ($highlight as $term)
		{
			$sql = str_replace($term, '<strong>' . $term . '</strong>', $sql);
		}

		return $sql;
	}

	/**
	 * Return text representation of the query
	 * 返回查询的文本表示形式
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getQuery();
	}

	//--------------------------------------------------------------------
}
