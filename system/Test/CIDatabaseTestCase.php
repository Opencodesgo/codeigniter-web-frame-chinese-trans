<?php
/**
 * CodeIgniter，测试，CI 数据库测试用例
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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Exceptions\ConfigException;
use Config\Database;
use Config\Migrations;
use Config\Services;

/**
 * CIDatabaseTestCase
 * CI 数据库测试用例。
 */
abstract class CIDatabaseTestCase extends CIUnitTestCase
{
	/**
	 * Should run db migration?
	 * 应该运行db迁移吗？
	 *
	 * @var boolean
	 */
	protected $migrate = true;

	/**
	 * Should run db migration only once?
	 * 应该只运行db迁移一次吗？
	 *
	 * @var boolean
	 */
	protected $migrateOnce = false;

	/**
	 * Is db migration done once or more than once?
	 * 数据库迁移是一次完成还是多次完成？
	 *
	 * @var boolean
	 */
	private static $doneMigration = false;

	/**
	 * Should run seeding only once?
	 * 应该只运行一次播种吗？
	 *
	 * @var boolean
	 */
	protected $seedOnce = false;

	/**
	 * Is seeding done once or more than once?
	 * 播种是一次还是多次？
	 *
	 * @var boolean
	 */
	private static $doneSeed = false;

	/**
	 * Should the db be refreshed before test?
	 * 测试前应该刷新数据库吗？
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The seed file(s) used for all tests within this test case.
	 * Should be fully-namespaced or relative to $basePath
	 * 本测试用例中所有测试所使用的种子文件。应为完全命名空间，或相对于 $basePath。
	 *
	 * @var string|array
	 */
	protected $seed = '';

	/**
	 * The path to the seeds directory.
	 * Allows overriding the default application directories.
	 * 通往种子目录的路径。可覆盖默认的应用程序目录。
	 *
	 * @var string
	 */
	protected $basePath = SUPPORTPATH . 'Database';

	/**
	 * The namespace(s) to help us find the migration classes.
	 * Empty is equivalent to running `spark migrate -all`.
	 * Note that running "all" runs migrations in date order,
	 * but specifying namespaces runs them in namespace order (then date)
	 *
	 * @var string|array|null
	 */
	protected $namespace = 'Tests\Support';

	/**
	 * The name of the database group to connect to.
	 * If not present, will use the defaultGroup.
	 * 要连接的数据库组名称。如果不存在，则使用默认组。
	 *
	 * @var string
	 */
	protected $DBGroup = 'tests';

	/**
	 * Our database connection.
	 * 我们的数据库连接
	 *
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * Migration Runner instance.
	 * 迁移运行程序实例
	 *
	 * @var MigrationRunner|mixed
	 */
	protected $migrations;

	/**
	 * Seeder instance
	 * 播种机实例
	 *
	 * @var \CodeIgniter\Database\Seeder
	 */
	protected $seeder;

	/**
	 * Stores information needed to remove any
	 * rows inserted via $this->hasInDatabase();
	 *
	 * @var array
	 */
	protected $insertCache = [];

	//--------------------------------------------------------------------

	/**
	 * Load any database test dependencies.
	 * 加载任何数据库测试依赖项
	 */
	public function loadDependencies()
	{
		if ($this->db === null)
		{
			$this->db = Database::connect($this->DBGroup);
			$this->db->initialize();
		}

		if ($this->migrations === null)
		{
			// Ensure that we can run migrations
			$config          = new Migrations();
			$config->enabled = true;

			$this->migrations = Services::migrations($config, $this->db);
			$this->migrations->setSilent(false);
		}

		if ($this->seeder === null)
		{
			$this->seeder = Database::seeder($this->DBGroup);
			$this->seeder->setSilent(true);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that the database is cleaned up to a known state
	 * before each test runs.
	 * 确保在每次测试运行前，数据库已清理至已知状态。
	 *
	 * @throws ConfigException
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->loadDependencies();

		$this->setUpMigrate();
		$this->setUpSeed();
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate on setUp
	 * 根据安装进行迁移
	 */
	protected function setUpMigrate()
	{
		if ($this->migrateOnce === false || self::$doneMigration === false)
		{
			if ($this->refresh === true)
			{
				$this->regressDatabase();

				// Reset counts on faked items
				Fabricator::resetCounts();
			}

			$this->migrateDatabase();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Seed on setUp
	 * 设置种子
	 */
	protected function setUpSeed()
	{
		if ($this->seedOnce === false || self::$doneSeed === false)
		{
			$this->runSeeds();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any required cleanup after the test, like
	 * removing any rows inserted via $this->hasInDatabase()
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		if (! empty($this->insertCache))
		{
			foreach ($this->insertCache as $row)
			{
				$this->db->table($row[0])
						->where($row[1])
						->delete();
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Run seeds as defined by the class
	 * 运行类定义的种子
	 */
	protected function runSeeds()
	{
		if (! empty($this->seed))
		{
			if (! empty($this->basePath))
			{
				$this->seeder->setPath(rtrim($this->basePath, '/') . '/Seeds');
			}

			$seeds = is_array($this->seed) ? $this->seed : [$this->seed];
			foreach ($seeds as $seed)
			{
				$this->seed($seed);
			}
		}

		self::$doneSeed = true;
	}

	//--------------------------------------------------------------------

	/**
	 * Regress migrations as defined by the class
	 * 由类定义的回归迁移
	 */
	protected function regressDatabase()
	{
		if ($this->migrate === false)
		{
			return;
		}

		// If no namespace was specified then rollback all
		if (empty($this->namespace))
		{
			$this->migrations->setNamespace(null);
			$this->migrations->regress(0, 'tests');
		}

		// Regress each specified namespace
		else
		{
			$namespaces = is_array($this->namespace) ? $this->namespace : [$this->namespace];

			foreach ($namespaces as $namespace)
			{
				$this->migrations->setNamespace($namespace);
				$this->migrations->regress(0, 'tests');
			}
		}
	}

	/**
	 * Run migrations as defined by the class
	 * 运行类定义的迁移
	 */
	protected function migrateDatabase()
	{
		if ($this->migrate === false)
		{
			return;
		}

		// If no namespace was specified then migrate all
		if (empty($this->namespace))
		{
			$this->migrations->setNamespace(null);
			$this->migrations->latest('tests');
			self::$doneMigration = true;
		}
		// Run migrations for each specified namespace
		else
		{
			$namespaces = is_array($this->namespace) ? $this->namespace : [$this->namespace];

			foreach ($namespaces as $namespace)
			{
				$this->migrations->setNamespace($namespace);
				$this->migrations->latest('tests');
				self::$doneMigration = true;
			}
		}
	}

	/**
	 * Seeds that database with a specific seeder.
	 * 使用特定的种子器为该数据库播种
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function seed(string $name)
	{
		$this->seeder->call($name);
	}

	//--------------------------------------------------------------------
	// Database Test Helpers	数据库测试助手
	//--------------------------------------------------------------------

	/**
	 * Asserts that records that match the conditions in $where do
	 * not exist in the database.
	 * 断言与 $where 条件匹配的记录在数据库中不存在
	 *
	 * @param string $table
	 * @param array  $where
	 *
	 * @return void
	 */
	public function dontSeeInDatabase(string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertTrue($count === 0, 'Row was found in database');
	}

	//--------------------------------------------------------------------

	/**
	 * Asserts that records that match the conditions in $where DO
	 * exist in the database.
	 * 断言与 $where 条件匹配的记录在数据库中确实存在
	 *
	 * @param string $table
	 * @param array  $where
	 *
	 * @return void
	 * @throws DatabaseException
	 */
	public function seeInDatabase(string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertTrue($count > 0, 'Row not found in database: ' . $this->db->showLastQuery());
	}

	//--------------------------------------------------------------------

	/**
	 * Fetches a single column from a database row with criteria
	 * matching $where.
	 * 根据与 $where 匹配的条件，从数据库行中获取单个列。
	 *
	 * @param string $table
	 * @param string $column
	 * @param array  $where
	 *
	 * @return boolean
	 * @throws DatabaseException
	 */
	public function grabFromDatabase(string $table, string $column, array $where)
	{
		$query = $this->db->table($table)
				->select($column)
				->where($where)
				->get();

		$query = $query->getRow();

		return $query->$column ?? false;
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts a row into to the database. This row will be removed
	 * after the test has run.
	 * 将一行数据插入数据库。测试运行结束后，该行数据将被删除。
	 *
	 * @param string $table
	 * @param array  $data
	 *
	 * @return boolean
	 */
	public function hasInDatabase(string $table, array $data)
	{
		$this->insertCache[] = [
			$table,
			$data,
		];

		return $this->db->table($table)
					->insert($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Asserts that the number of rows in the database that match $where
	 * is equal to $expected.
	 * 断言与 $where 匹配的数据库行数等于 $expected
	 *
	 * @param integer $expected
	 * @param string  $table
	 * @param array   $where
	 *
	 * @return void
	 * @throws DatabaseException
	 */
	public function seeNumRecords(int $expected, string $table, array $where)
	{
		$count = $this->db->table($table)
				->where($where)
				->countAllResults();

		$this->assertEquals($expected, $count, 'Wrong number of matching rows in database.');
	}

	//--------------------------------------------------------------------

	/**
	 * Reset $doneMigration and $doneSeed
	 * 重置$doneMigration和$doneSeed
	 *
	 * @afterClass
	 */
	public static function resetMigrationSeedCount()
	{
		self::$doneMigration = false;
		self::$doneSeed      = false;
	}
}
