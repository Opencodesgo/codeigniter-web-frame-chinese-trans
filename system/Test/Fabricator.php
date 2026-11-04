<?php
/**
 * CodeIgniter，测试，Fabricator
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Model;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Fabricator
 * 制作者。
 *
 * Bridge class for using Faker to create example data based on
 * model specifications.
 * 用于根据模型规格使用 Faker 创建示例数据的桥接类。
 */
class Fabricator
{
	/**
	 * Array of counts for fabricated items
	 * 捏造项目的计数数组
	 *
	 * @var array
	 */
	protected static $tableCounts = [];

	/**
	 * Locale-specific Faker instance
	 * 特定于地区的Faker实例
	 *
	 * @var Generator
	 */
	protected $faker;

	/**
	 * Model instance (can be non-framework if it follows framework design)
	 * 模型实例（如果遵循框架设计，可以是非框架的）
	 *
	 * @var CodeIgniter\Model|object
	 */
	protected $model;

	/**
	 * Locale used to initialize Faker
	 * 用于初始化Faker的区域设置
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * Map of properties and their formatter to use
	 * 属性及其要使用的格式化程序的映射
	 *
	 * @var array|null
	 */
	protected $formatters;

	/**
	 * Date fields present in the model
	 * 出现在模型中的日期字段
	 *
	 * @var array
	 */
	protected $dateFields = [];

	/**
	 * Array of data to add or override faked versions
	 * 数组的数据添加或覆盖假版本
	 *
	 * @var array
	 */
	protected $overrides = [];

	/**
	 * Array of single-use data to override faked versions
	 * 用于覆盖伪造版本的一次性数据数组
	 *
	 * @var array|null
	 */
	protected $tempOverrides;

	/**
	 * Default formatter to use when nothing is detected
	 * 当未检测到任何内容时使用的默认格式化程序
	 *
	 * @var string
	 */
	public $defaultFormatter = 'word';

	//--------------------------------------------------------------------

	/**
	 * Store the model instance and initialize Faker to the locale.
	 * 存储模型实例并将Faker初始化为语言环境
	 *
	 * @param string|object $model      Instance or classname of the model to use
	 * @param array|null    $formatters Array of property => formatter
	 * @param string|null   $locale     Locale for Faker provider
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($model, array $formatters = null, string $locale = null)
	{
		if (is_string($model))
		{
			// Create a new model instance
			$model = model($model, false);
		}

		if (! is_object($model))
		{
			throw new InvalidArgumentException(lang('Fabricator.invalidModel'));
		}

		$this->model = $model;

		// If no locale was specified then use the App default
		// 如果没有指定区域设置,那么使用App默认。
		if (is_null($locale))
		{
			$locale = config('App')->defaultLocale;
		}

		// There is no easy way to retrieve the locale from Faker so we will store it
		// 没有简单的方法来从Faker检索区域,所以我们将存储它。
		$this->locale = $locale;

		// Create the locale-specific Generator
		$this->faker = Factory::create($this->locale);

		// Determine eligible date fields
		foreach (['createdField', 'updatedField', 'deletedField'] as $field)
		{
			if (! empty($this->model->$field))
			{
				$this->dateFields[] = $this->model->$field;
			}
		}

		// Set the formatters
		$this->setFormatters($formatters);
	}

	//--------------------------------------------------------------------

	/**
	 * Reset internal counts
	 * 重置内部计数
	 */
	public static function resetCounts()
	{
		self::$tableCounts = [];
	}

	/**
	 * Get the count for a specific table
	 * 获取特定表的计数
	 *
	 * @param string $table Name of the target table
	 *
	 * @return integer
	 */
	public static function getCount(string $table): int
	{
		return empty(self::$tableCounts[$table]) ? 0 : self::$tableCounts[$table];
	}

	/**
	 * Set the count for a specific table
	 * 设置特定表的计数
	 *
	 * @param string  $table Name of the target table
	 * @param integer $count Count value
	 *
	 * @return integer  The new count value
	 */
	public static function setCount(string $table, int $count): int
	{
		self::$tableCounts[$table] = $count;
		return $count;
	}

	/**
	 * Increment the count for a table
	 * 增加表的计数
	 *
	 * @param string $table Name of the target table
	 *
	 * @return integer  The new count value
	 */
	public static function upCount(string $table): int
	{
		return self::setCount($table, self::getCount($table) + 1);
	}

	/**
	 * Decrement the count for a table
	 * 递减表的计数
	 *
	 * @param string $table Name of the target table
	 *
	 * @return integer  The new count value
	 */
	public static function downCount(string $table): int
	{
		return self::setCount($table, self::getCount($table) - 1);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the model instance
	 * 返回模型实例
	 *
	 * @return object  Framework or compatible model
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Returns the locale
	 * 返回区域设置
	 *
	 * @return string
	 */
	public function getLocale(): string
	{
		return $this->locale;
	}

	/**
	 * Returns the Faker generator
	 * 返回Faker生成器
	 *
	 * @return Generator
	 */
	public function getFaker(): Generator
	{
		return $this->faker;
	}

	//--------------------------------------------------------------------

	/**
	 * Return and reset tempOverrides
	 * 返回并重置temoverrides
	 *
	 * @return array
	 */
	public function getOverrides(): array
	{
		$overrides = $this->tempOverrides ?? $this->overrides;

		$this->tempOverrides = $this->overrides;

		return $overrides;
	}

	/**
	 * Set the overrides, once or persistent
	 * 设置覆盖，一次或持久。
	 *
	 * @param array   $overrides Array of [field => value]
	 * @param boolean $persist   Whether these overrides should persist through the next operation
	 *
	 * @return $this
	 */
	public function setOverrides(array $overrides = [], $persist = true): self
	{
		if ($persist)
		{
			$this->overrides = $overrides;
		}

		$this->tempOverrides = $overrides;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current formatters
	 * 返回当前格式化程序
	 *
	 * @return array|null
	 */
	public function getFormatters(): ?array
	{
		return $this->formatters;
	}

	/**
	 * Set the formatters to use. Will attempt to autodetect if none are available.
	 * 设置要使用的格式化程序。如果没有可用的，将尝试自动检测。
	 *
	 * @param array|null $formatters Array of [field => formatter], or null to detect
	 *
	 * @return $this
	 */
	public function setFormatters(array $formatters = null): self
	{
		if (! is_null($formatters))
		{
			$this->formatters = $formatters;
		}
		elseif (method_exists($this->model, 'fake'))
		{
			$this->formatters = null;
		}
		else
		{
			$formatters = $this->detectFormatters();
		}

		return $this;
	}

	/**
	 * Try to identify the appropriate Faker formatter for each field.
	 * 尝试为每个字段确定适当的Faker格式化程序
	 *
	 * @return $this
	 */
	protected function detectFormatters(): self
	{
		$this->formatters = [];

		if (! empty($this->model->allowedFields))
		{
			foreach ($this->model->allowedFields as $field)
			{
				$this->formatters[$field] = $this->guessFormatter($field);
			}
		}

		return $this;
	}

	/**
	 * Guess at the correct formatter to match a field name.
	 * 猜测匹配字段名的正确格式化程序
	 *
	 * @param string $field Name of the field
	 *
	 * @return string  Name of the formatter
	 */
	protected function guessFormatter($field): string
	{
		// First check for a Faker formatter of the same name - covers things like "email"
		try
		{
			$this->faker->getFormatter($field);
			return $field;
		}
		catch (InvalidArgumentException $e)
		{
			// No match, keep going
		}

		// Next look for known model fields
		if (in_array($field, $this->dateFields, true))
		{
			switch ($this->model->dateFormat)
			{
				case 'datetime':
					return 'date';
				case 'date':
					return 'date';
				case 'int':
					return 'unixTime';
			}
		}
		elseif ($field === $this->model->primaryKey)
		{
			return 'numberBetween';
		}

		// Check some common partials
		foreach (['email', 'name', 'title', 'text', 'date', 'url'] as $term)
		{
			if (stripos($field, $term) !== false)
			{
				return $term;
			}
		}

		if (stripos($field, 'phone') !== false)
		{
			return 'phoneNumber';
		}

		// Nothing left, use the default
		return $this->defaultFormatter;
	}

	//--------------------------------------------------------------------

	/**
	 * Generate new entities with faked data
	 * 生成带有伪造数据的新实体
	 *
	 * @param integer|null $count Optional number to create a collection
	 *
	 * @return array|object  An array or object (based on returnType), or an array of returnTypes
	 */
	public function make(int $count = null)
	{
		// If a singleton was requested then go straight to it
		if (is_null($count))
		{
			return $this->model->returnType === 'array'
				? $this->makeArray()
				: $this->makeObject();
		}

		$return = [];

		for ($i = 0; $i < $count; $i++)
		{
			$return[] = $this->model->returnType === 'array'
				? $this->makeArray()
				: $this->makeObject();
		}

		return $return;
	}

	/**
	 * Generate an array of faked data
	 * 生成一个伪造数据数组
	 *
	 * @return array  An array of faked data
	 *
	 * @throws RuntimeException
	 */
	public function makeArray()
	{
		if (! is_null($this->formatters))
		{
			$result = [];

			foreach ($this->formatters as $field => $formatter)
			{
				$result[$field] = $this->faker->{$formatter};
			}
		}

		// If no formatters were defined then look for a model fake() method
		// 如果没有定义任何格式化程序,那么就可以查找模型伪()方法。
		elseif (method_exists($this->model, 'fake'))
		{
			$result = $this->model->fake($this->faker);

			// This should cover entities
			if (method_exists($result, 'toArray'))
			{
				$result = $result->toArray();
			}
			// Try to cast it
			else
			{
				$result = (array) $result;
			}
		}

		// Nothing left to do but give up
		else
		{
			throw new RuntimeException(lang('Fabricator.missingFormatters'));
		}

		// Replace overridden fields
		return array_merge($result, $this->getOverrides());
	}

	/**
	 * Generate an object of faked data
	 * 生成伪造数据对象
	 *
	 * @param string|null $className Class name of the object to create; null to use model default
	 *
	 * @return object  An instance of the class with faked data
	 *
	 * @throws RuntimeException
	 */
	public function makeObject(string $className = null): object
	{
		if (is_null($className))
		{
			if ($this->model->returnType === 'object' || $this->model->returnType === 'array')
			{
				$className = 'stdClass';
			}
			else
			{
				$className = $this->model->returnType;
			}
		}

		// If using the model's fake() method then check it for the correct return type
		if (is_null($this->formatters) && method_exists($this->model, 'fake'))
		{
			$result = $this->model->fake($this->faker);

			if ($result instanceof $className)
			{
				// Set overrides manually
				foreach ($this->getOverrides() as $key => $value)
				{
					$result->{$key} = $value;
				}

				return $result;
			}
		}

		// Get the array values and apply them to the object
		$array  = $this->makeArray();
		$object = new $className();

		// Check for the entity method
		if (method_exists($object, 'fill'))
		{
			$object->fill($array);
		}
		else
		{
			foreach ($array as $key => $value)
			{
				$object->{$key} = $value;
			}
		}

		return $object;
	}

	//--------------------------------------------------------------------

	/**
	 * Generate new entities from the database
	 * 从数据库生成新的实体
	 *
	 * @param integer|null $count Optional number to create a collection
	 * @param boolean      $mock  Whether to execute or mock the insertion
	 *
	 * @return array|object  An array or object (based on returnType), or an array of returnTypes
	 *
	 * @throws FrameworkException
	 */
	public function create(int $count = null, bool $mock = false)
	{
		// Intercept mock requests
		if ($mock)
		{
			return $this->createMock($count);
		}

		$ids = [];

		// Iterate over new entities and insert each one, storing insert IDs
		// 对新实体进行迭代,并插入每个实体,存储插入id。
		foreach ($this->make($count ?? 1) as $result)
		{
			if ($id = $this->model->insert($result, true))
			{
				$ids[] = $id;
				self::upCount($this->model->table);
				continue;
			}

			throw FrameworkException::forFabricatorCreateFailed($this->model->table, implode(' ', $this->model->errors() ?? []));
		}

		// If the model defines a "withDeleted" method for handling soft deletes then use it
		if (method_exists($this->model, 'withDeleted'))
		{
			$this->model->withDeleted();
		}

		return $this->model->find(is_null($count) ? reset($ids) : $ids);
	}

	/**
	 * Generate new database entities without actually inserting them
	 * 生成新的数据库实体，而不实际插入它们。
	 *
	 * @param integer|null $count Optional number to create a collection
	 *
	 * @return array|object  An array or object (based on returnType), or an array of returnTypes
	 */
	protected function createMock(int $count = null)
	{
		switch ($this->model->dateFormat)
		{
			case 'datetime':
				$datetime = date('Y-m-d H:i:s');
			case 'date':
				$datetime = date('Y-m-d');
			default:
				$datetime = time();
		}

		// Determine which fields we will need
		// 确定我们需要哪些字段
		$fields = [];

		if (! empty($this->model->useTimestamps))
		{
			$fields[$this->model->createdField] = $datetime; // @phpstan-ignore-line
			$fields[$this->model->updatedField] = $datetime; // @phpstan-ignore-line
		}

		if (! empty($this->model->useSoftDeletes))
		{
			$fields[$this->model->deletedField] = null; // @phpstan-ignore-line
		}

		// Iterate over new entities and add the necessary fields
		// 遍历新实体并添加必要的字段
		$return = [];
		foreach ($this->make($count ?? 1) as $i => $result)
		{
			// Set the ID
			$fields[$this->model->primaryKey] = $i;

			// Merge fields
			if (is_array($result))
			{
				$result = array_merge($result, $fields);
			}
			else
			{
				foreach ($fields as $key => $value)
				{
					$result->{$key} = $value;
				}
			}

			$return[] = $result;
		}

		return is_null($count) ? reset($return) : $return;
	}
}
