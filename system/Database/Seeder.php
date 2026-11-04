<?php
/**
 * CodeIgniter，数据库，播种机
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

use CodeIgniter\CLI\CLI;
use Config\Database;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;

/**
 * Class Seeder
 * 类播种机。
 */
class Seeder
{
	/**
	 * The name of the database group to use.
	 * 要使用的数据库组的名称
	 *
	 * @var string
	 */
	protected $DBGroup;

	/**
	 * Where we can find the Seed files.
	 * 在那里我们可以找到种子文件
	 *
	 * @var string
	 */
	protected $seedPath;

	/**
	 * An instance of the main Database configuration
	 * 主数据库配置的实例
	 *
	 * @var Database
	 */
	protected $config;

	/**
	 * Database Connection instance
	 * 数据库连接实例
	 *
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * Database Forge instance.
	 * 数据库伪造实例
	 *
	 * @var Forge
	 */
	protected $forge;

	/**
	 * If true, will not display CLI messages.
	 * 如果为true，将不显示CLI消息。
	 *
	 * @var boolean
	 */
	protected $silent = false;

	/**
	 * Faker Generator instance.
	 * Faker生成器实例
	 *
	 * @var \Faker\Generator|null
	 */
	private static $faker;

	/**
	 * Seeder constructor.
	 * 播种机的构造函数
	 *
	 * @param Database            $config
	 * @param BaseConnection|null $db
	 */
	public function __construct(Database $config, BaseConnection $db = null)
	{
		$this->seedPath = $config->filesPath ?? APPPATH . 'Database/';

		if (empty($this->seedPath))
		{
			throw new InvalidArgumentException('Invalid filesPath set in the Config\Database.');
		}

		$this->seedPath = rtrim($this->seedPath, '\\/') . '/Seeds/';

		if (! is_dir($this->seedPath))
		{
			throw new InvalidArgumentException('Unable to locate the seeds directory. Please check Config\Database::filesPath');
		}

		$this->config = &$config;

		$db = $db ?? Database::connect($this->DBGroup);

		$this->db    = &$db;
		$this->forge = Database::forge($this->DBGroup);
	}

	/**
	 * Gets the Faker Generator instance.
	 * 获取Faker生成器实例
	 *
	 * @return Generator|null
	 */
	public static function faker(): ?Generator
	{
		if (self::$faker === null && class_exists(Factory::class))
		{
			self::$faker = Factory::create();
		}

		return self::$faker;
	}

	/**
	 * Loads the specified seeder and runs it.
	 * 加载指定的播种器并运行它
	 *
	 * @param string $class
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return void
	 */
	public function call(string $class)
	{
		if (empty($class))
		{
			throw new InvalidArgumentException('No seeder was specified.');
		}

		$path = str_replace('.php', '', $class) . '.php';

		// If we have namespaced class, simply try to load it.
		if (strpos($class, '\\') !== false)
		{
			/**
			 * @var Seeder
			 */
			$seeder = new $class($this->config);
		}
		// Otherwise, try to load the class manually.
		// 否则，尝试手动加载类。
		else
		{
			$path = $this->seedPath . $path;

			if (! is_file($path))
			{
				throw new InvalidArgumentException('The specified seeder is not a valid file: ' . $path);
			}

			// Assume the class has the correct namespace
			// @codeCoverageIgnoreStart
			$class = APP_NAMESPACE . '\Database\Seeds\\' . $class;

			if (! class_exists($class, false))
			{
				require_once $path;
			}

			/**
			 * @var Seeder
			 */
			$seeder = new $class($this->config);
			// @codeCoverageIgnoreEnd
		}

		$seeder->setSilent($this->silent)->run();

		unset($seeder);

		if (is_cli() && ! $this->silent)
		{
			CLI::write("Seeded: {$class}", 'green');
		}
	}

	/**
	 * Sets the location of the directory that seed files can be located in.
	 * 设置可放置种子文件的目录位置
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public function setPath(string $path)
	{
		$this->seedPath = rtrim($path, '\\/') . '/';

		return $this;
	}

	/**
	 * Sets the silent treatment.
	 * 设置静音处理
	 *
	 * @param boolean $silent
	 *
	 * @return $this
	 */
	public function setSilent(bool $silent)
	{
		$this->silent = $silent;

		return $this;
	}

	/**
	 * Run the database seeds. This is where the magic happens.
	 * 运行数据库种子。这就是奇迹发生的地方。
	 *
	 * Child classes must implement this method and take care
	 * of inserting their data here.
	 * 子类必须实现此方法，并负责在此处插入其数据。
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore
	 */
	public function run()
	{
	}
}
