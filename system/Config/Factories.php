<?php
/**
 * CodeIgniter，配置，工厂
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

use Config\Services;

/**
 * Factories for creating instances.
 * 用于创建实例的工厂。
 *
 * Factories allows dynamic loading of components by their path
 * and name. The "shared instance" implementation provides a
 * large performance boost and helps keep code clean of lengthy
 * instantiation checks.
 * Factories 允许通过路径和名称动态加载组件。
 *
 * @method static \CodeIgniter\Model models(...$arguments)
 * @method static \Config\BaseConfig config(...$arguments)
 */
class Factories
{
	/**
	 * Store of component-specific options, usually
	 * from CodeIgniter\Config\Factory.
	 * 存储特定组件的选项，通常来自 CodeIgniter\Config\Factory。
	 *
	 * @var array<string, array>
	 */
	protected static $options = [];

	/**
	 * Explicit options for the Config
	 * component to prevent logic loops.
	 * 配置组件的显式选项，用于防止逻辑循环。
	 *
	 * @var array<string, mixed>
	 */
	private static $configOptions = [
		'component'  => 'config',
		'path'       => 'Config',
		'instanceOf' => null,
		'getShared'  => true,
		'preferApp'  => true,
	];

	/**
	 * Mapping of class basenames (no namespace) to
	 * their instances.
	 * 将类的基名（无命名空间）映射为其实例
	 *
	 * @var array<string, string[]>
	 */
	protected static $basenames = [];

	/**
	 * Store for instances of any component that
	 * has been requested as "shared".
	 * A multi-dimensional array with components as
	 * keys to the array of name-indexed instances.
	 * 存储所有被请求为“共享”的组件实例。
	 * 一个多维数组，其元素作为名称-索引实例数组的键。
	 *
	 * @var array<string, array>
	 */
	protected static $instances = [];

	//--------------------------------------------------------------------

	/**
	 * Loads instances based on the method component name. Either
	 * creates a new instance or returns an existing shared instance.
	 * 根据方法组件名称加载实例。创建一个新的实例，或返回一个已有的共享实例。
	 *
	 * @param string $component
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $component, array $arguments)
	{
		// First argument is the name, second is options
		// 第一个参数是名称,第二个是选项。
		$name    = trim(array_shift($arguments), '\\ ');
		$options = array_shift($arguments) ?? [];

		// Determine the component-specific options
		// 确定特定于组件的选项
		$options = array_merge(self::getOptions(strtolower($component)), $options);

		if (! $options['getShared'])
		{
			if ($class = self::locateClass($options, $name))
			{
				return new $class(...$arguments);
			}

			return null;
		}

		$basename = self::getBasename($name);

		// Check for an existing instance
		// 检查现有实例
		if (isset(self::$basenames[$options['component']][$basename]))
		{
			$class = self::$basenames[$options['component']][$basename];
		}
		else
		{
			// Try to locate the class
			if (! $class = self::locateClass($options, $name))
			{
				return null;
			}

			self::$instances[$options['component']][$class]    = new $class(...$arguments);
			self::$basenames[$options['component']][$basename] = $class;
		}

		return self::$instances[$options['component']][$class];
	}

	/**
	 * Finds a component class
	 * 查找组件类
	 *
	 * @param array  $options The array of component-specific directives
	 * @param string $name    Class name, namespace optional
	 *
	 * @return string|null
	 */
	protected static function locateClass(array $options, string $name): ?string
	{
		// Check for low-hanging fruit
		// 检查low-hanging水果
		if (class_exists($name, false) && self::verifyPreferApp($options, $name) && self::verifyInstanceOf($options, $name))
		{
			return $name;
		}

		// Determine the relative class names we need
		// 确定我们需要的相关类名
		$basename = self::getBasename($name);
		$appname  = $options['component'] === 'config'
			? 'Config\\' . $basename
			: rtrim(APP_NAMESPACE, '\\') . '\\' . $options['path'] . '\\' . $basename;

		// If an App version was requested then see if it verifies
		// 如果请求一个应用程序版本,那么看看它是否验证。
		if ($options['preferApp'] && class_exists($appname) && self::verifyInstanceOf($options, $name))
		{
			return $appname;
		}

		// If we have ruled out an App version and the class exists then try it
		// 如果我们已经排除了一个应用程序版本,然后可以尝试它。
		if (class_exists($name) && self::verifyInstanceOf($options, $name))
		{
			return $name;
		}

		// Have to do this the hard way...
		$locator = Services::locator();

		// Check if the class was namespaced
		if (strpos($name, '\\') !== false)
		{
			if (! $file = $locator->locateFile($name, $options['path']))
			{
				return null;
			}

			$files = [$file];
		}
		// No namespace? Search for it
		else
		{
			// Check all namespaces, prioritizing App and modules
			if (! $files = $locator->search($options['path'] . DIRECTORY_SEPARATOR . $name))
			{
				return null;
			}
		}

		// Check all files for a valid class
		foreach ($files as $file)
		{
			$class = $locator->getClassname($file);

			if ($class && self::verifyInstanceOf($options, $class))
			{
				return $class;
			}
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Verifies that a class & config satisfy the "preferApp" option
	 * 验证类和配置满足“优惠应用”选项
	 *
	 * @param array  $options The array of component-specific directives
	 * @param string $name    Class name, namespace optional
	 *
	 * @return boolean
	 */
	protected static function verifyPreferApp(array $options, string $name): bool
	{
		// Anything without that restriction passes
		if (! $options['preferApp'])
		{
			return true;
		}

		// Special case for Config since its App namespace is actually \Config
		if ($options['component'] === 'config')
		{
			return strpos($name, 'Config') === 0;
		}

		return strpos($name, APP_NAMESPACE) === 0;
	}

	/**
	 * Verifies that a class & config satisfy the "instanceOf" option
	 * 验证类和配置是否满足“instanceOf”选项
	 *
	 * @param array  $options The array of component-specific directives
	 * @param string $name    Class name, namespace optional
	 *
	 * @return boolean
	 */
	protected static function verifyInstanceOf(array $options, string $name): bool
	{
		// Anything without that restriction passes
		if (! $options['instanceOf'])
		{
			return true;
		}

		return is_a($name, $options['instanceOf'], true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the component-specific configuration
	 * 返回组件特定的配置
	 *
	 * @param string $component Lowercase, plural component name
	 *
	 * @return array<string, mixed>
	 */
	public static function getOptions(string $component): array
	{
		$component = strtolower($component);

		// Check for a stored version
		if (isset(self::$options[$component]))
		{
			return self::$options[$component];
		}

		// Handle Config as a special case to prevent logic loops
		if ($component === 'config')
		{
			$values = self::$configOptions;
		}
		// Load values from the best Factory configuration (will include Registrars)
		else
		{
			$values = config('Factory')->$component ?? [];
		}

		return self::setOptions($component, $values);
	}

	/**
	 * Normalizes, stores, and returns the configuration for a specific component
	 * 规范化、存储并返回特定组件的配置。
	 *
	 * @param string $component Lowercase, plural component name
	 * @param array  $values
	 *
	 * @return array<string, mixed> The result after applying defaults and normalization
	 */
	public static function setOptions(string $component, array $values): array
	{
		// Allow the config to replace the component name, to support "aliases"
		$values['component'] = strtolower($values['component'] ?? $component);

		// Reset this component so instances can be rediscovered with the updated config
		self::reset($values['component']);

		// If no path was available then use the component
		$values['path'] = trim($values['path'] ?? ucfirst($values['component']), '\\ ');

		// Add defaults for any missing values
		$values = array_merge(Factory::$default, $values);

		// Store the result to the supplied name and potential alias
		self::$options[$component]           = $values;
		self::$options[$values['component']] = $values;

		return $values;
	}

	/**
	 * Resets the static arrays, optionally just for one component
	 * 重置静态数组，可选地只针对一个组件。
	 *
	 * @param string $component Lowercase, plural component name
	 */
	public static function reset(string $component = null)
	{
		if ($component)
		{
			unset(static::$options[$component]);
			unset(static::$basenames[$component]);
			unset(static::$instances[$component]);

			return;
		}

		static::$options   = [];
		static::$basenames = [];
		static::$instances = [];
	}

	/**
	 * Helper method for injecting mock instances
	 * 用于注入模拟实例的助手方法
	 *
	 * @param string $component Lowercase, plural component name
	 * @param string $name      The name of the instance
	 * @param object $instance
	 */
	public static function injectMock(string $component, string $name, object $instance)
	{
		// Force a configuration to exist for this component
		// 强制此组件存在配置
		$component = strtolower($component);
		self::getOptions($component);

		$class    = get_class($instance);
		$basename = self::getBasename($name);

		self::$instances[$component][$class]    = $instance;
		self::$basenames[$component][$basename] = $class;
	}

	/**
	 * Gets a basename from a class name, namespaced or not.
	 * 从类名（无论是否有名称空间）获取基名
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getBasename(string $name): string
	{
		// Determine the basename
		if ($basename = strrchr($name, '\\'))
		{
			return substr($basename, 1);
		}

		return $name;
	}
}
