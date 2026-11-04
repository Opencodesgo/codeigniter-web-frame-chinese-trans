<?php
/**
 * CodeIgniter，I18n，时间
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\I18n;

use CodeIgniter\I18n\Exceptions\I18nException;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use IntlCalendar;
use IntlDateFormatter;
use Locale;

/**
 * Class Time
 * 时间类。
 *
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @property string $date
 */
class Time extends DateTime
{
	/**
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * @var string
	 */
	protected $locale;

	/**
	 * Format to use when displaying datetime through __toString
	 * 通过__toString显示日期时间时使用的格式
	 *
	 * @var string
	 */
	protected $toStringFormat = 'yyyy-MM-dd HH:mm:ss';

	/**
	 * Used to check time string to determine if it is relative time or not....
	 * 用于检查时间字符串以确定它是否是相对时间....
	 *
	 * @var string
	 */
	protected static $relativePattern = '/this|next|last|tomorrow|yesterday|midnight|today|[+-]|first|last|ago/i';

	/**
	 * @var \CodeIgniter\I18n\Time|DateTime|null
	 */
	protected static $testNow;

	//--------------------------------------------------------------------
	// Constructors	构造函数
	//--------------------------------------------------------------------

	/**
	 * Time constructor.
	 * 时间构造函数
	 *
	 * @param string|null              $time
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @throws Exception
	 */
	public function __construct(string $time = null, $timezone = null, string $locale = null)
	{
		// If no locale was provided, grab it from Locale (set by IncomingRequest for web requests)
		$this->locale = ! empty($locale) ? $locale : Locale::getDefault();

		// If a test instance has been provided, use it instead.
		if (is_null($time) && static::$testNow instanceof Time)
		{
			if (empty($timezone))
			{
				$timezone = static::$testNow->getTimezone();
			}

			$time = static::$testNow->toDateTimeString();
		}

		$timezone       = ! empty($timezone) ? $timezone : date_default_timezone_get();
		$this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);

		if (! empty($time))
		{
			// If the time string was a relative string (i.e. 'next Tuesday')
			// then we need to adjust the time going in so that we have a current
			// timezone to work with.
			if (is_string($time) && static::hasRelativeKeywords($time))
			{
				$instance = new DateTime('now', $this->timezone);
				$instance->modify($time);

				$time = $instance->format('Y-m-d H:i:s');
			}
		}

		parent::__construct($time, $this->timezone);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance with the timezone set.
	 * 返回具有时区集的新Time实例
	 *
	 * @param string|DateTimeZone|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function now($timezone = null, string $locale = null)
	{
		return new Time(null, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance while parsing a datetime string.
	 * 在解析日期时间字符串时返回一个新的时间实例。
	 *
	 * Example:
	 *  $time = Time::parse('first day of December 2008');
	 *
	 * @param string                   $datetime
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function parse(string $datetime, $timezone = null, string $locale = null)
	{
		return new Time($datetime, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Return a new time with the time set to midnight.
	 * 返回设置为午夜的新时间
	 *
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function today($timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d 00:00:00'), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance set to midnight yesterday morning.
	 * 返回设置为昨天上午午夜的实例
	 *
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function yesterday($timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d 00:00:00', strtotime('-1 day')), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance set to midnight tomorrow morning.
	 * 返回实例设置为明天早上午夜
	 *
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function tomorrow($timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d 00:00:00', strtotime('+1 day')), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance based on the year, month and day. If any of those three
	 * are left empty, will default to the current value.
	 * 根据年、月和日返回一个新的实例。如果这三个值中的任意一个为空，则默认使用当前值。
	 *
	 * @param integer|null             $year
	 * @param integer|null             $month
	 * @param integer|null             $day
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function createFromDate(int $year = null, int $month = null, int $day = null, $timezone = null, string $locale = null)
	{
		return static::create($year, $month, $day, null, null, null, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the date set to today, and the time set to the values passed in.
	 * 返回一个新的实例，日期设置为今天，时间设置为传入的值。
	 *
	 * @param integer|null             $hour
	 * @param integer|null             $minutes
	 * @param integer|null             $seconds
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function createFromTime(int $hour = null, int $minutes = null, int $seconds = null, $timezone = null, string $locale = null)
	{
		return static::create(null, null, null, $hour, $minutes, $seconds, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the date time values individually set.
	 * 返回一个新的实例，其中分别设置了日期和时间值。
	 *
	 * @param integer|null             $year
	 * @param integer|null             $month
	 * @param integer|null             $day
	 * @param integer|null             $hour
	 * @param integer|null             $minutes
	 * @param integer|null             $seconds
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function create(int $year = null, int $month = null, int $day = null, int $hour = null, int $minutes = null, int $seconds = null, $timezone = null, string $locale = null)
	{
		$year    = is_null($year) ? date('Y') : $year;
		$month   = is_null($month) ? date('m') : $month;
		$day     = is_null($day) ? date('d') : $day;
		$hour    = empty($hour) ? 0 : $hour;
		$minutes = empty($minutes) ? 0 : $minutes;
		$seconds = empty($seconds) ? 0 : $seconds;

		return new Time(date('Y-m-d H:i:s', strtotime("{$year}-{$month}-{$day} {$hour}:{$minutes}:{$seconds}")), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a replacement for DateTime's own createFromFormat function, that provides
	 * more flexible timeZone handling
	 * 提供了一个替代 DateTime 自带的 createFromFormat 函数的方案，能够更灵活地处理时区。
	 *
	 * @param string                   $format
	 * @param string                   $datetime
	 * @param DateTimeZone|string|null $timeZone
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function createFromFormat($format, $datetime, $timeZone = null)
	{
		if (! $date = parent::createFromFormat($format, $datetime))
		{
			throw I18nException::forInvalidFormat($format);
		}

		return new Time($date->format('Y-m-d H:i:s'), $timeZone);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the datetime set based on the provided UNIX timestamp.
	 * 返回一个新的实例，其datetime设置基于所提供的UNIX时间戳。
	 *
	 * @param integer                  $timestamp
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function createFromTimestamp(int $timestamp, $timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d H:i:s', $timestamp), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Takes an instance of DateTime and returns an instance of Time with it's same values.
	 * 获取DateTime的一个实例，并返回一个具有相同值的Time实例。
	 *
	 * @param DateTime    $dateTime
	 * @param string|null $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function instance(DateTime $dateTime, string $locale = null)
	{
		$date     = $dateTime->format('Y-m-d H:i:s');
		$timezone = $dateTime->getTimezone();

		return new Time($date, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Converts the current instance to a mutable DateTime object.
	 * 将当前实例转换为可变DateTime对象
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	public function toDateTime()
	{
		$dateTime = new DateTime('', $this->getTimezone());
		$dateTime->setTimestamp(parent::getTimestamp());

		return $dateTime;
	}

	//--------------------------------------------------------------------
	// For Testing	为测试
	//--------------------------------------------------------------------

	/**
	 * Creates an instance of Time that will be returned during testing
	 * when calling 'Time::now' instead of the current time.
	 *
	 * @param Time|DateTime|string|null $datetime
	 * @param DateTimeZone|string|null  $timezone
	 * @param string|null               $locale
	 *
	 * @throws Exception
	 */
	public static function setTestNow($datetime = null, $timezone = null, string $locale = null)
	{
		// Reset the test instance
		if (is_null($datetime))
		{
			static::$testNow = null;
			return;
		}

		// Convert to a Time instance
		if (is_string($datetime))
		{
			$datetime = new Time($datetime, $timezone, $locale);
		}
		elseif ($datetime instanceof DateTime && ! $datetime instanceof Time)
		{
			$datetime = new Time($datetime->format('Y-m-d H:i:s'), $timezone);
		}

		static::$testNow = $datetime;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether we have a testNow instance saved.
	 * 返回是否保存了testNow实例
	 *
	 * @return boolean
	 */
	public static function hasTestNow(): bool
	{
		return ! is_null(static::$testNow);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Getters	获得者
	//--------------------------------------------------------------------

	/**
	 * Returns the localized Year
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getYear(): string
	{
		return $this->toLocalizedString('y');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the localized Month
	 * 返回本地化后的月份
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getMonth(): string
	{
		return $this->toLocalizedString('M');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized day of the month.
	 * 返回本地化后的月份的日期
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getDay(): string
	{
		return $this->toLocalizedString('d');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized hour (in 24-hour format).
	 * 返回本地化后的小时（24小时格式）
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getHour(): string
	{
		return $this->toLocalizedString('H');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized minutes in the hour.
	 * 返回小时中本地化的分钟
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getMinute(): string
	{
		return $this->toLocalizedString('m');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized seconds
	 * 返回本地化的秒数
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getSecond(): string
	{
		return $this->toLocalizedString('s');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the day of the week
	 * 返回星期几的索引
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getDayOfWeek(): string
	{
		return $this->toLocalizedString('c');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the day of the year
	 * 返回一年中某一天的索引
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getDayOfYear(): string
	{
		return $this->toLocalizedString('D');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the week in the month
	 * 返回该月中该周的索引
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getWeekOfMonth(): string
	{
		return $this->toLocalizedString('W');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the week in the year
	 * 返回一年中该周的索引
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getWeekOfYear(): string
	{
		return $this->toLocalizedString('w');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the age in years from the "current" date and 'now'
	 * 返回从“当前”日期到“现在”的年龄（以年为单位）
	 *
	 * @return integer
	 * @throws Exception
	 */
	public function getAge()
	{
		$now  = Time::now()->getTimestamp();
		$time = $this->getTimestamp();

		// future dates have no age
		return max(0, date('Y', $now) - date('Y', $time));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of the current quarter for the year.
	 * 返回当年当前季度的数字
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getQuarter(): string
	{
		return $this->toLocalizedString('Q');
	}

	//--------------------------------------------------------------------

	/**
	 * Are we in daylight savings time currently?
	 * 我们现在是夏令时吗？
	 *
	 * @return boolean
	 */
	public function getDst(): bool
	{
		// grab the transactions that would affect today
		$start       = strtotime('-1 year', $this->getTimestamp());
		$end         = strtotime('+2 year', $start);
		$transitions = $this->timezone->getTransitions($start, $end);

		$daylightSaving = false;
		foreach ($transitions as $transition)
		{
			if ($transition['time'] > $this->format('U'))
			{
				$daylightSaving = (bool) $transition['isdst'] ?? $daylightSaving;
				break;
			}
		}
		return $daylightSaving;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns boolean whether the passed timezone is the same as
	 * the local timezone.
	 * 返回布尔值，表示传入的时区是否与本地时区相同。
	 *
	 * @return boolean
	 */
	public function getLocal(): bool
	{
		$local = date_default_timezone_get();

		return $local === $this->timezone->getName();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns boolean whether object is in UTC.
	 * 返回布尔值，对象是否为UTC格式。
	 *
	 * @return boolean
	 */
	public function getUtc(): bool
	{
		return $this->getOffset() === 0;
	}

	/**
	 * Returns the name of the current timezone.
	 * 返回当前时区的名称
	 *
	 * @return string
	 */
	public function getTimezoneName(): string
	{
		return $this->timezone->getName();
	}

	//--------------------------------------------------------------------
	// Setters	设置器
	//--------------------------------------------------------------------

	/**
	 * Sets the current year for this instance.
	 * 设置此实例的当前年份
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setYear($value)
	{
		return $this->setValue('year', $value);
	}

	/**
	 * Sets the month of the year.
	 * 设置一年中的月份
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setMonth($value)
	{
		if (is_numeric($value) && ($value < 1 || $value > 12))
		{
			throw I18nException::forInvalidMonth($value);
		}

		if (is_string($value) && ! is_numeric($value))
		{
			$value = date('m', strtotime("{$value} 1 2017"));
		}

		return $this->setValue('month', $value);
	}

	/**
	 * Sets the day of the month.
	 * 设置月份的日期
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setDay($value)
	{
		if ($value < 1 || $value > 31)
		{
			throw I18nException::forInvalidDay($value);
		}

		$date    = $this->getYear() . '-' . $this->getMonth();
		$lastDay = date('t', strtotime($date));
		if ($value > $lastDay)
		{
			throw I18nException::forInvalidOverDay($lastDay, $value);
		}

		return $this->setValue('day', $value);
	}

	/**
	 * Sets the hour of the day (24 hour cycle)
	 * 设置一天中的小时（24小时周期）
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setHour($value)
	{
		if ($value < 0 || $value > 23)
		{
			throw I18nException::forInvalidHour($value);
		}

		return $this->setValue('hour', $value);
	}

	/**
	 * Sets the minute of the hour
	 * 设置小时中的分钟
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setMinute($value)
	{
		if ($value < 0 || $value > 59)
		{
			throw I18nException::forInvalidMinutes($value);
		}

		return $this->setValue('minute', $value);
	}

	/**
	 * Sets the second of the minute.
	 * 设置每分钟的第二秒
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setSecond($value)
	{
		if ($value < 0 || $value > 59)
		{
			throw I18nException::forInvalidSeconds($value);
		}

		return $this->setValue('second', $value);
	}

	/**
	 * Helper method to do the heavy lifting of the 'setX' methods.
	 * 帮助器方法来完成‘setX’方法的繁重工作
	 *
	 * @param string  $name
	 * @param integer $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	protected function setValue(string $name, $value)
	{
		list($year, $month, $day, $hour, $minute, $second) = explode('-', $this->format('Y-n-j-G-i-s'));
		$$name                                             = $value;

		return Time::create(
			(int) $year,
			(int) $month,
			(int) $day,
			(int) $hour,
			(int) $minute,
			(int) $second,
			$this->getTimezoneName(),
			$this->locale
		);
	}

	/**
	 * Returns a new instance with the revised timezone.
	 * 返回具有修改后时区的新实例
	 *
	 * @param string|DateTimeZone $timezone
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setTimezone($timezone)
	{
		$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
		return Time::instance($this->toDateTime()->setTimezone($timezone), $this->locale);
	}

	/**
	 * Returns a new instance with the date set to the new timestamp.
	 * 返回一个新的实例，并将日期设置为新的时间戳。
	 *
	 * @param integer $timestamp
	 *
	 * @return Time
	 * @throws Exception
	 */
	public function setTimestamp($timestamp)
	{
		$time = date('Y-m-d H:i:s', $timestamp);

		return Time::parse($time, $this->timezone, $this->locale);
	}

	//--------------------------------------------------------------------
	// Add/Subtract	加减
	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance with $seconds added to the time.
	 * 返回一个新的Time实例，并将$seconds添加到时间中。
	 *
	 * @param integer $seconds
	 *
	 * @return static
	 */
	public function addSeconds(int $seconds)
	{
		$time = clone($this);

		return $time->add(DateInterval::createFromDateString("{$seconds} seconds"));
	}

	/**
	 * Returns a new Time instance with $minutes added to the time.
	 * 返回一个新的Time实例，并在时间上添加了$minutes。
	 *
	 * @param integer $minutes
	 *
	 * @return static
	 */
	public function addMinutes(int $minutes)
	{
		$time = clone($this);

		return $time->add(DateInterval::createFromDateString("{$minutes} minutes"));
	}

	/**
	 * Returns a new Time instance with $hours added to the time.
	 * 返回一个新的Time实例，并将$hours添加到时间中。
	 *
	 * @param integer $hours
	 *
	 * @return static
	 */
	public function addHours(int $hours)
	{
		$time = clone($this);

		return $time->add(DateInterval::createFromDateString("{$hours} hours"));
	}

	/**
	 * Returns a new Time instance with $days added to the time.
	 * 返回一个新的Time实例，并在时间上添加了$days。
	 *
	 * @param integer $days
	 *
	 * @return static
	 */
	public function addDays(int $days)
	{
		$time = clone($this);

		return $time->add(DateInterval::createFromDateString("{$days} days"));
	}

	/**
	 * Returns a new Time instance with $months added to the time.
	 * 返回一个新的Time实例，并在时间上添加了$months。
	 *
	 * @param integer $months
	 *
	 * @return static
	 */
	public function addMonths(int $months)
	{
		$time = clone($this);

		return $time->add(DateInterval::createFromDateString("{$months} months"));
	}

	/**
	 * Returns a new Time instance with $years added to the time.
	 * 返回一个新的Time实例，并在时间上添加了$years。
	 *
	 * @param integer $years
	 *
	 * @return static
	 */
	public function addYears(int $years)
	{
		$time = clone($this);

		return $time->add(DateInterval::createFromDateString("{$years} years"));
	}

	/**
	 * Returns a new Time instance with $seconds subtracted from the time.
	 * 返回一个新的Time实例，从时间中减去$seconds。
	 *
	 * @param integer $seconds
	 *
	 * @return static
	 */
	public function subSeconds(int $seconds)
	{
		$time = clone($this);

		return $time->sub(DateInterval::createFromDateString("{$seconds} seconds"));
	}

	/**
	 * Returns a new Time instance with $minutes subtracted from the time.
	 * 返回一个新的Time实例，其中从时间中减去$minutes。
	 *
	 * @param integer $minutes
	 *
	 * @return static
	 */
	public function subMinutes(int $minutes)
	{
		$time = clone($this);

		return $time->sub(DateInterval::createFromDateString("{$minutes} minutes"));
	}

	/**
	 * Returns a new Time instance with $hours subtracted from the time.
	 * 返回一个新的Time实例，其中从时间中减去$hours。
	 *
	 * @param integer $hours
	 *
	 * @return static
	 */
	public function subHours(int $hours)
	{
		$time = clone($this);

		return $time->sub(DateInterval::createFromDateString("{$hours} hours"));
	}

	/**
	 * Returns a new Time instance with $days subtracted from the time.
	 * 返回一个新的Time实例，其中从时间中减去$days。
	 *
	 * @param integer $days
	 *
	 * @return static
	 */
	public function subDays(int $days)
	{
		$time = clone($this);

		return $time->sub(DateInterval::createFromDateString("{$days} days"));
	}

	/**
	 * Returns a new Time instance with $months subtracted from the time.
	 * 返回一个新的Time实例，其中从时间中减去$months。
	 *
	 * @param integer $months
	 *
	 * @return static
	 */
	public function subMonths(int $months)
	{
		$time = clone($this);

		return $time->sub(DateInterval::createFromDateString("{$months} months"));
	}

	/**
	 * Returns a new Time instance with $hours subtracted from the time.
	 * 返回一个新的Time实例，其中从时间中减去$hours。
	 *
	 * @param integer $years
	 *
	 * @return static
	 */
	public function subYears(int $years)
	{
		$time = clone($this);

		return $time->sub(DateInterval::createFromDateString("{$years} years"));
	}

	//--------------------------------------------------------------------
	// Formatters	格式化程序
	//--------------------------------------------------------------------

	/**
	 * Returns the localized value of the date in the format 'Y-m-d H:i:s'
	 *
	 * @throws Exception
	 */
	public function toDateTimeString()
	{
		return $this->toLocalizedString('yyyy-MM-dd HH:mm:ss');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a localized version of the date in Y-m-d format.
	 * 以Y-m-d格式返回日期的本地化版本
	 *
	 * @return string
	 * @throws Exception
	 */
	public function toDateString()
	{
		return $this->toLocalizedString('yyyy-MM-dd');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a localized version of the date in nicer date format:
	 * 以更好的日期格式返回日期的本地化版本：
	 *
	 *  i.e. Apr 1, 2017
	 *
	 * @return string
	 * @throws Exception
	 */
	public function toFormattedDateString()
	{
		return $this->toLocalizedString('MMM d, yyyy');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a localized version of the time in nicer date format:
	 *
	 *  i.e. 13:20:33
	 *
	 * @return string
	 * @throws Exception
	 */
	public function toTimeString()
	{
		return $this->toLocalizedString('HH:mm:ss');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the localized value of this instance in $format.
	 *
	 * @param string|null $format
	 *
	 * @return string|boolean
	 * @throws Exception
	 */
	public function toLocalizedString(string $format = null)
	{
		$format = $format ?? $this->toStringFormat;

		return IntlDateFormatter::formatObject($this->toDateTime(), $format, $this->locale);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Comparison	比较
	//--------------------------------------------------------------------

	/**
	 * Determines if the datetime passed in is equal to the current instance.
	 * Equal in this case means that they represent the same moment in time,
	 * and are not required to be in the same timezone, as both times are
	 * converted to UTC and compared that way.
	 * 确定传入的日期时间是否等于当前实例。
	 *
	 * @param Time|DateTime|string $testTime
	 * @param string|null          $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function equals($testTime, string $timezone = null): bool
	{
		$testTime = $this->getUTCObject($testTime, $timezone);

		$ourTime = $this->toDateTime()
				->setTimezone(new DateTimeZone('UTC'))
				->format('Y-m-d H:i:s');

		return $testTime->format('Y-m-d H:i:s') === $ourTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that the times are identical, taking timezone into account.
	 * 将时区考虑在内，确保时间相同。
	 *
	 * @param Time|DateTime|string $testTime
	 * @param string|null          $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function sameAs($testTime, string $timezone = null): bool
	{
		if ($testTime instanceof DateTime)
		{
			$testTime = $testTime->format('Y-m-d H:i:s');
		}
		elseif (is_string($testTime))
		{
			$timezone = $timezone ?: $this->timezone;
			$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
			$testTime = new DateTime($testTime, $timezone);
			$testTime = $testTime->format('Y-m-d H:i:s');
		}

		$ourTime = $this->toDateTimeString();

		return $testTime === $ourTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the current instance's time is before $testTime,
	 * after converting to UTC.
	 * 确定当前实例的时间是否在转换为UTC后早于 $testTime
	 *
	 * @param mixed       $testTime
	 * @param string|null $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function isBefore($testTime, string $timezone = null): bool
	{
		$testTime = $this->getUTCObject($testTime, $timezone)->getTimestamp();
		$ourTime  = $this->getTimestamp();

		return $ourTime < $testTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the current instance's time is after $testTime,
	 * after converting in UTC.
	 * 确定当前实例的时间是否在转换为UTC后晚于 $testTime
	 *
	 * @param mixed       $testTime
	 * @param string|null $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function isAfter($testTime, string $timezone = null): bool
	{
		$testTime = $this->getUTCObject($testTime, $timezone)->getTimestamp();
		$ourTime  = $this->getTimestamp();

		return $ourTime > $testTime;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Differences	差异
	//--------------------------------------------------------------------

	/**
	 * Returns a text string that is easily readable that describes
	 * how long ago, or how long from now, a date is, like:
	 *
	 *  - 3 weeks ago
	 *  - in 4 days
	 *  - 6 hours ago
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function humanize()
	{
		$now  = IntlCalendar::fromDateTime(Time::now($this->timezone)->toDateTimeString());
		$time = $this->getCalendar()->getTime();

		$years   = $now->fieldDifference($time, IntlCalendar::FIELD_YEAR);
		$months  = $now->fieldDifference($time, IntlCalendar::FIELD_MONTH);
		$days    = $now->fieldDifference($time, IntlCalendar::FIELD_DAY_OF_YEAR);
		$hours   = $now->fieldDifference($time, IntlCalendar::FIELD_HOUR_OF_DAY);
		$minutes = $now->fieldDifference($time, IntlCalendar::FIELD_MINUTE);

		$phrase = null;

		if ($years !== 0)
		{
			$phrase = lang('Time.years', [abs($years)]);
			$before = $years < 0;
		}
		elseif ($months !== 0)
		{
			$phrase = lang('Time.months', [abs($months)]);
			$before = $months < 0;
		}
		elseif ($days !== 0 && (abs($days) >= 7))
		{
			$weeks  = ceil($days / 7);
			$phrase = lang('Time.weeks', [abs($weeks)]);
			$before = $days < 0;
		}
		elseif ($days !== 0)
		{
			$before = $days < 0;

			// Yesterday/Tomorrow special cases
			if (abs($days) === 1)
			{
				return $before ? lang('Time.yesterday') : lang('Time.tomorrow');
			}

			$phrase = lang('Time.days', [abs($days)]);
		}
		elseif ($hours !== 0)
		{
			$phrase = lang('Time.hours', [abs($hours)]);
			$before = $hours < 0;
		}
		elseif ($minutes !== 0)
		{
			$phrase = lang('Time.minutes', [abs($minutes)]);
			$before = $minutes < 0;
		}
		else
		{
			return lang('Time.now');
		}

		return $before ? lang('Time.ago', [$phrase]) : lang('Time.inFuture', [$phrase]);
	}

	/**
	 * @param mixed       $testTime
	 * @param string|null $timezone
	 *
	 * @return TimeDifference
	 * @throws Exception
	 */
	public function difference($testTime, string $timezone = null)
	{
		$testTime = $this->getUTCObject($testTime, $timezone);
		$ourTime  = $this->getUTCObject($this);

		return new TimeDifference($ourTime, $testTime);
	}

	//--------------------------------------------------------------------
	// Utilities	工具
	//--------------------------------------------------------------------

	/**
	 * Returns a Time instance with the timezone converted to UTC.
	 * 返回时区转换为UTC的Time实例
	 *
	 * @param mixed       $time
	 * @param string|null $timezone
	 *
	 * @return DateTime|static
	 * @throws Exception
	 */
	public function getUTCObject($time, string $timezone = null)
	{
		if ($time instanceof Time)
		{
			$time = $time->toDateTime()
					->setTimezone(new DateTimeZone('UTC'));
		}
		elseif ($time instanceof DateTime)
		{
			$time = $time->setTimezone(new DateTimeZone('UTC'));
		}
		elseif (is_string($time))
		{
			$timezone = $timezone ?: $this->timezone;
			$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
			$time     = new DateTime($time, $timezone);
			$time     = $time->setTimezone(new DateTimeZone('UTC'));
		}

		return $time;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the IntlCalendar object used for this object,
	 * taking into account the locale, date, etc.
	 * 返回用于此对象的 IntlCalendar 对象，会考虑本地化、日期等因素。
	 *
	 * Primarily used internally to provide the difference and comparison functions,
	 * but available for public consumption if they need it.
	 *
	 * @return IntlCalendar
	 * @throws Exception
	 */
	public function getCalendar()
	{
		return IntlCalendar::fromDateTime($this->toDateTimeString());
	}

	//--------------------------------------------------------------------

	/**
	 * Check a time string to see if it includes a relative date (like 'next Tuesday').
	 * 检查一个时间字符串,看看它是否包含一个相关日期(比如下一个星期二)。
	 *
	 * @param string $time
	 *
	 * @return boolean
	 */
	protected static function hasRelativeKeywords(string $time): bool
	{
		// skip common format with a '-' in it
		if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $time) !== 1)
		{
			return preg_match(static::$relativePattern, $time) > 0;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a short format version of the datetime.
	 * 输出日期时间的短格式版本
	 *
	 * @return string
	 * @throws Exception
	 */
	public function __toString(): string
	{
		return IntlDateFormatter::formatObject($this->toDateTime(), $this->toStringFormat, $this->locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Allow for property-type access to any getX method...
	 * 允许对任何getX方法进行属性类型访问……
	 *
	 * Note that we cannot use this for any of our setX methods,
	 * as they return new Time objects, but the __set ignores
	 * return values.
	 * See http://php.net/manual/en/language.oop5.overloading.php
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);

		if (method_exists($this, $method))
		{
			return $this->$method();
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Allow for property-type checking to any getX method...
	 * 允许对任何getX方法进行属性类型检查…
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function __isset($name): bool
	{
		$method = 'get' . ucfirst($name);

		return method_exists($this, $method);
	}

	//--------------------------------------------------------------------

	/**
	 * This is called when we unserialize the Time object.
	 * 这个函数在反序列化Time对象时调用
	 */
	public function __wakeup()
	{
		/**
		 * Prior to unserialization, this is a string.
		 * 在反序列化之前，这是一个字符串。
		 *
		 * @var string $timezone
		 */
		$timezone = $this->timezone;

		$this->timezone = new DateTimeZone($timezone);
		parent::__construct($this->date, $this->timezone);
	}
}
