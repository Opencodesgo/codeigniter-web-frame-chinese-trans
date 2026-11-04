<?php
/**
 * CodeIgniter，测试，反射助手
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

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

/**
 * Testing helper.
 * 测试助手，改为反射助手。
 */
trait ReflectionHelper
{
	/**
	 * Find a private method invoker.
	 * 找到一个私有方法调用器
	 *
	 * @param object|string $obj    object or class name
	 * @param string        $method method name
	 *
	 * @return Closure
	 * @throws ReflectionException
	 */
	public static function getPrivateMethodInvoker($obj, $method)
	{
		$refMethod = new ReflectionMethod($obj, $method);
		$refMethod->setAccessible(true);
		$obj = (gettype($obj) === 'object') ? $obj : null;

		return function () use ($obj, $refMethod) {
			$args = func_get_args();
			return $refMethod->invokeArgs($obj, $args);
		};
	}

	/**
	 * Find an accessible property.
	 * 找到一个可访问的属性
	 *
	 * @param object|string $obj
	 * @param string        $property
	 *
	 * @return ReflectionProperty
	 * @throws ReflectionException
	 */
	private static function getAccessibleRefProperty($obj, $property)
	{
		if (is_object($obj))
		{
			$refClass = new ReflectionObject($obj);
		}
		else
		{
			$refClass = new ReflectionClass($obj);
		}

		$refProperty = $refClass->getProperty($property);
		$refProperty->setAccessible(true);

		return $refProperty;
	}

	/**
	 * Set a private property.
	 * 设置私有属性
	 *
	 * @param object|string $obj      object or class name
	 * @param string        $property property name
	 * @param mixed         $value    value
	 *
	 * @throws ReflectionException
	 */
	public static function setPrivateProperty($obj, $property, $value)
	{
		$refProperty = self::getAccessibleRefProperty($obj, $property);
		$refProperty->setValue($obj, $value);
	}

	/**
	 * Retrieve a private property.
	 * 检索私有属性
	 *
	 * @param object|string $obj      object or class name
	 * @param string        $property property name
	 *
	 * @return mixed value
	 * @throws ReflectionException
	 */
	public static function getPrivateProperty($obj, $property)
	{
		$refProperty = self::getAccessibleRefProperty($obj, $property);
		return is_string($obj) ? $refProperty->getValue() : $refProperty->getValue($obj);
	}
}
