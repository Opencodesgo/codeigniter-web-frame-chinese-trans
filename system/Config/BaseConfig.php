<?php
/**
 * CodeIgniter，配置，基本配置
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use Config\Encryption;
use Config\Modules;
use Config\Services;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Class BaseConfig
 * 基本配置类。
 *
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values
 * from the environment.
 * 本类不建议单独使用，它将尝试自动从环境获取值并填充子类的属性。
 *
 * These can be set within the .env file.
 */
class BaseConfig
{
	/**
	 * An optional array of classes that will act as Registrars
	 * for rapidly setting config class properties.
	 * 可选的类数组，用于快速设置配置类属性。
	 *
	 * @var array
	 */
	public static $registrars = [];

	/**
	 * Has module discovery happened yet?
	 * 模块发现已经开始了吗？
	 *
	 * @var boolean
	 */
	protected static $didDiscovery = false;

	/**
	 * The modules configuration.
	 * 模块配置
	 *
	 * @var Modules
	 */
	protected static $moduleConfig;

	/**
	 * Will attempt to get environment variables with names
	 * that match the properties of the child class.
	 * 尝试获取与子类属性名称匹配的环境变量。
	 *
	 * The "shortPrefix" is the lowercase-only config class name.
	 */
	public function __construct()
	{
		static::$moduleConfig = config('Modules');

		$properties  = array_keys(get_object_vars($this));
		$prefix      = static::class;
		$slashAt     = strrpos($prefix, '\\');
		$shortPrefix = strtolower(substr($prefix, $slashAt === false ? 0 : $slashAt + 1));

		foreach ($properties as $property)
		{
			$this->initEnvValue($this->$property, $property, $prefix, $shortPrefix);

			if ($this instanceof Encryption && $property === 'key')
			{
				// Handle hex2bin prefix
				if (strpos($this->$property, 'hex2bin:') === 0)
				{
					$this->$property = hex2bin(substr($this->$property, 8));
				}
				// Handle base64 prefix
				elseif (strpos($this->$property, 'base64:') === 0)
				{
					$this->$property = base64_decode(substr($this->$property, 7), true);
				}
			}
		}

		$this->registerProperties();
	}

	/**
	 * Initialization an environment-specific configuration setting
	 * 初始化特定于环境的配置设置
	 *
	 * @param mixed  $property
	 * @param string $name
	 * @param string $prefix
	 * @param string $shortPrefix
	 *
	 * @return mixed
	 */
	protected function initEnvValue(&$property, string $name, string $prefix, string $shortPrefix)
	{
		if (is_array($property))
		{
			foreach ($property as $key => $val)
			{
				$this->initEnvValue($property[$key], "{$name}.{$key}", $prefix, $shortPrefix);
			}
		}
		else
		{
			if (($value = $this->getEnvValue($name, $prefix, $shortPrefix)) !== false)
			{
				if (! is_null($value))
				{
					if ($value === 'false')
					{
						$value = false;
					}
					elseif ($value === 'true')
					{
						$value = true;
					}

					$property = is_bool($value) ? $value : trim($value, '\'"');
				}
			}
		}
		return $property;
	}

	/**
	 * Retrieve an environment-specific configuration setting
	 * 检索特定于环境的配置设置
	 *
	 * @param string $property
	 * @param string $prefix
	 * @param string $shortPrefix
	 *
	 * @return mixed
	 */
	protected function getEnvValue(string $property, string $prefix, string $shortPrefix)
	{
		$shortPrefix = ltrim($shortPrefix, '\\');
		switch (true)
		{
			case array_key_exists("{$shortPrefix}.{$property}", $_ENV):
				return $_ENV["{$shortPrefix}.{$property}"];
			case array_key_exists("{$shortPrefix}.{$property}", $_SERVER):
				return $_SERVER["{$shortPrefix}.{$property}"];
			case array_key_exists("{$prefix}.{$property}", $_ENV):
				return $_ENV["{$prefix}.{$property}"];
			case array_key_exists("{$prefix}.{$property}", $_SERVER):
				return $_SERVER["{$prefix}.{$property}"];
			default:
				$value = getenv($property);
				return $value === false ? null : $value;
		}
	}

	/**
	 * Provides external libraries a simple way to register one or more
	 * options into a config file.
	 * 为外部库提供了一种简单的方法，可将一个或多个选项注册到配置文件中。
	 *
	 * @throws ReflectionException
	 */
	protected function registerProperties()
	{
		if (! static::$moduleConfig->shouldDiscover('registrars'))
		{
			return;
		}

		if (! static::$didDiscovery)
		{
			$locator         = Services::locator();
			$registrarsFiles = $locator->search('Config/Registrar.php');

			foreach ($registrarsFiles as $file)
			{
				$className            = $locator->getClassname($file);
				static::$registrars[] = new $className();
			}

			static::$didDiscovery = true;
		}

		$shortName = (new ReflectionClass($this))->getShortName();

		// Check the registrar class for a method named after this class' shortName
		// 检查注册器类是否有以该类的shortName命名的方法
		foreach (static::$registrars as $callable)
		{
			// ignore non-applicable registrars
			if (! method_exists($callable, $shortName))
			{
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$properties = $callable::$shortName();

			if (! is_array($properties))
			{
				throw new RuntimeException('Registrars must return an array of properties and their values.');
			}

			foreach ($properties as $property => $value)
			{
				if (isset($this->$property) && is_array($this->$property) && is_array($value))
				{
					$this->$property = array_merge($this->$property, $value);
				}
				else
				{
					$this->$property = $value;
				}
			}
		}
	}
}
