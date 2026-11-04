<?php
/**
 * CodeIgniter，测试，接口，制作者模型
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Interfaces;

use Faker\Generator;
use ReflectionException;

/**
 * FabricatorModel
 * 制作者模型。
 *
 * An interface defining the required methods and properties
 * needed for a model to qualify for use with the Fabricator class.
 * While interfaces cannot enforce properties, the following
 * are required for use with Fabricator:
 * 一个定义了模型要具备的必要方法和属性的接口，以便该模型能够被 Fabricator 类使用。
 * 虽然接口无法强制要求属性，但以下内容是使用 Fabricator 所必需的：
 *
 * @property string $returnType
 * @property string $primaryKey
 * @property string $dateFormat
 */
interface FabricatorModel
{
	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 * 从 $this->table 中获取主键匹配 $id 的数据库行。
	 *
	 * @param mixed|array|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id = null);

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 * 将数据插入当前表。如果提供了对象，将尝试将其转换为数组。
	 *
	 * @param array|object $data
	 * @param boolean      $returnID Whether insert ID should be returned or not.
	 *
	 * @return integer|string|boolean
	 * @throws ReflectionException
	 */
	public function insert($data = null, bool $returnID = true);

	/**
	 * The following properties and methods are optional, but if present should
	 * adhere to their definitions.
	 * 以下属性和方法为可选，但如果存在，则必须遵循其定义。
	 *
	 * @property array  $allowedFields
	 * @property string $useSoftDeletes
	 * @property string $useTimestamps
	 * @property string $createdField
	 * @property string $updatedField
	 * @property string $deletedField
	 */

	/*
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the softdeletes settings. Can be used for all find* methods.
	 * 设置 $useSoftDeletes 值，以便临时覆盖软删除的配置。可用于所有 find* 方法。
	 *
	 * @param boolean $val
	 *
	 * @return Model
	 */
	// public function withDeleted($val = true);

	/**
	 * Faked data for Fabricator.
	 * 伪造数据的制造者
	 *
	 * @param Generator $faker
	 *
	 * @return array|object
	 */
	// public function fake(Generator &$faker);
}
