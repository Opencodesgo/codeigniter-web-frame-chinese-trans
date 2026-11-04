<?php
/**
 * CodeIgniter，数据库，模型工厂
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

use CodeIgniter\Config\Factories;

/**
 * Returns new or shared Model instances
 * 返回新的或共享的模型实例
 *
 * @deprecated Use CodeIgniter\Config\Factories::models()
 */
class ModelFactory
{
	/**
	 * Creates new Model instances or returns a shared instance
	 * 创建新的Model实例或返回一个共享实例
	 *
	 * @param string              $name       Model name, namespace optional
	 * @param boolean             $getShared  Use shared instance
	 * @param ConnectionInterface $connection
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true, ConnectionInterface $connection = null)
	{
		return Factories::models($name, ['getShared' => $getShared], $connection);
	}

	/**
	 * Helper method for injecting mock instances while testing.
	 * 在测试时注入模拟实例的助手方法
	 *
	 * @param string $name
	 * @param object $instance
	 */
	public static function injectMock(string $name, $instance)
	{
		Factories::injectMock('models', $name, $instance);
	}

	/**
	 * Resets the static arrays
	 * 重置静态数组
	 */
	public static function reset()
	{
		Factories::reset('models');
	}
}
