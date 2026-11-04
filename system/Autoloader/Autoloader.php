<?php
/**
 * CodeIgniter，自动加载器，Autoloader
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Autoloader;

use Config\Autoload;
use Config\Modules;
use InvalidArgumentException;

/**
 * CodeIgniter Autoloader
 * CodeIgniter自动加载器
 *
 * An autoloader that uses both PSR4 autoloading, and traditional classmaps.
 * 一个同时使用PSR4自动加载和传统类映射的自动加载器。
 *
 * Given a foo-bar package of classes in the file system at the following paths:
 *
 *      /path/to/packages/foo-bar/
 *          /src
 *              Baz.php         # Foo\Bar\Baz
 *              Qux/
 *                  Quux.php    # Foo\Bar\Qux\Quux
 *
 * you can add the path to the configuration array that is passed in the constructor.
 * The Config array consists of 2 primary keys, both of which are associative arrays:
 * 'psr4', and 'classmap'.
 *
 *      $Config = [
 *          'psr4' => [
 *              'Foo\Bar'   => '/path/to/packages/foo-bar'
 *          ],
 *          'classmap' => [
 *              'MyClass'   => '/path/to/class/file.php'
 *          ]
 *      ];
 *
 * Example:
 *
 *      <?php
 *      // our configuration array
 *      $Config = [ ... ];
 *      $loader = new \CodeIgniter\Autoloader\Autoloader($Config);
 *
 *      // register the autoloader
 *      $loader->register();
 */
class Autoloader
{
	/**
	 * Stores namespaces as key, and path as values.
	 * 将名称空间存储为键，并将路径存储为值。
	 *
	 * @var array
	 */
	protected $prefixes = [];

	/**
	 * Stores class name as key, and path as values.
	 * 将类名存储为键，将路径存储为值。
	 *
	 * @var array
	 */
	protected $classmap = [];

	//--------------------------------------------------------------------

	/**
	 * Reads in the configuration array (described above) and stores
	 * the valid parts that we'll need.
	 * 读取配置数组（如上所述），并存储我们需要的有效部分。
	 *
	 * @param Autoload $config
	 * @param Modules  $modules
	 *
	 * @return $this
	 */
	public function initialize(Autoload $config, Modules $modules)
	{
		// We have to have one or the other, though we don't enforce the need
		// to have both present in order to work.
		// 我们必须具备其中一种，尽管我们并不强制要求两者都必须在场才能工作。
		if (empty($config->psr4) && empty($config->classmap))
		{
			throw new InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
		}

		if (isset($config->psr4))
		{
			$this->addNamespace($config->psr4);
		}

		if (isset($config->classmap))
		{
			$this->classmap = $config->classmap;
		}

		// Should we load through Composer's namespaces, also?
		// 我们是否也应该通过Composer的名称空间进行加载？
		if ($modules->discoverInComposer)
		{
			$this->discoverComposerNamespaces();
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Register the loader with the SPL autoloader stack.
	 * 将加载程序注册到SPL自动加载程序堆栈
	 */
	public function register()
	{
		// Prepend the PSR4  autoloader for maximum performance.
		// 前置PSR4自动装弹器以获得最佳性能
		spl_autoload_register([$this, 'loadClass'], true, true); // @phpstan-ignore-line

		// Now prepend another loader for the files in our class map.

		// @phpstan-ignore-next-line
		spl_autoload_register(function ($class) {
			if (empty($this->classmap[$class]))
			{
				return false;
			}

			include_once $this->classmap[$class];
		}, true, // Throw exception
			true // Prepend
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Registers namespaces with the autoloader.
	 * 使用自动加载器注册命名空间
	 *
	 * @param array|string $namespace
	 * @param string       $path
	 *
	 * @return $this
	 */
	public function addNamespace($namespace, string $path = null)
	{
		if (is_array($namespace))
		{
			foreach ($namespace as $prefix => $path)
			{
				$prefix = trim($prefix, '\\');

				if (is_array($path))
				{
					foreach ($path as $dir)
					{
						$this->prefixes[$prefix][] = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR;
					}

					continue;
				}

				$this->prefixes[$prefix][] = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
			}
		}
		else
		{
			$this->prefixes[trim($namespace, '\\')][] = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get namespaces with prefixes as keys and paths as values.
	 * 获取以前缀作为键、以路径作为值的名称空间。
	 *
	 * If a prefix param is set, returns only paths to the given prefix.
	 * 如果设置前缀param,则只返回给定前缀的路径。
	 *
	 * @param string|null $prefix
	 *
	 * @return array
	 */
	public function getNamespace(string $prefix = null)
	{
		if ($prefix === null)
		{
			return $this->prefixes;
		}

		return $this->prefixes[trim($prefix, '\\')] ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single namespace from the psr4 settings.
	 * 从psr4设置中删除单个名称空间
	 *
	 * @param string $namespace
	 *
	 * @return $this
	 */
	public function removeNamespace(string $namespace)
	{
		if (isset($this->prefixes[trim($namespace, '\\')]))
		{
			unset($this->prefixes[trim($namespace, '\\')]);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for a given class name.
	 * 为给定的类名加载类文件
	 *
	 * @param string $class The fully qualified class name.
	 *
	 * @return string|false The mapped file on success, or boolean false
	 *                          on failure.
	 */
	public function loadClass(string $class)
	{
		$class = trim($class, '\\');
		$class = str_ireplace('.php', '', $class);

		$mapped_file = $this->loadInNamespace($class);

		// Nothing? One last chance by looking
		// in common CodeIgniter folders.
		if (! $mapped_file)
		{
			$mapped_file = $this->loadLegacy($class);
		}

		return $mapped_file;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the class file for a given class name.
	 * 为给定的类名加载类文件
	 *
	 * @param string $class The fully-qualified class name
	 *
	 * @return string|false The mapped file name on success, or boolean false on fail
	 */
	protected function loadInNamespace(string $class)
	{
		if (strpos($class, '\\') === false)
		{
			$class    = 'Config\\' . $class;
			$filePath = APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
			$filename = $this->includeFile($filePath);

			if ($filename)
			{
				return $filename;
			}

			return false;
		}

		foreach ($this->prefixes as $namespace => $directories)
		{
			foreach ($directories as $directory)
			{
				$directory = rtrim($directory, '\\/');

				if (strpos($class, $namespace) === 0)
				{
					$filePath = $directory . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))) . '.php';
					$filename = $this->includeFile($filePath);

					if ($filename)
					{
						return $filename;
					}
				}
			}
		}

		// never found a mapped file
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to load the class from common locations in previous
	 * version of CodeIgniter, namely 'app/Libraries', and
	 * 'app/Models'.
	 * 尝试从 CodeIgniter 早期版本的常见位置加载类，即 'app/Libraries' 和 'app/Models'。
	 *
	 * @param string $class The class name. This typically should NOT have a namespace.
	 *
	 * @return mixed    The mapped file name on success, or boolean false on failure
	 */
	protected function loadLegacy(string $class)
	{
		// If there is a namespace on this class, then
		// we cannot load it from traditional locations.
		if (strpos($class, '\\') !== false)
		{
			return false;
		}

		$paths = [
			APPPATH . 'Controllers/',
			APPPATH . 'Libraries/',
			APPPATH . 'Models/',
		];

		$class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

		foreach ($paths as $path)
		{
			if ($file = $this->includeFile($path . $class))
			{
				return $file;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * A central way to include a file. Split out primarily for testing purposes.
	 * 包含文件的中心方式。划分主要是为了测试的目的。
	 *
	 * @param string $file
	 *
	 * @return string|false The filename on success, false if the file is not loaded
	 */
	protected function includeFile(string $file)
	{
		$file = $this->sanitizeFilename($file);

		if (is_file($file))
		{
			include_once $file;

			return $file;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Sanitizes a filename, replacing spaces with dashes.
	 * 清理文件名，用破折号替换空格。
	 *
	 * Removes special characters that are illegal in filenames on certain
	 * operating systems and special characters requiring special escaping
	 * to manipulate at the command line. Replaces spaces and consecutive
	 * dashes with a single dash. Trim period, dash and underscore from beginning
	 * and end of filename.
	 *
	 * @param string $filename
	 *
	 * @return string       The sanitized filename
	 */
	public function sanitizeFilename(string $filename): string
	{
		// Only allow characters deemed safe for POSIX portable filenames.
		// Plus the forward slash for directory separators since this might
		// be a path.
		// http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap03.html#tag_03_278
		// Modified to allow backslash and colons for on Windows machines.
		$filename = preg_replace('/[^0-9\p{L}\s\/\-\_\.\:\\\\]/u', '', $filename);

		// Clean up our filename edges.
		$filename = trim($filename, '.-_');

		return $filename;
	}

	//--------------------------------------------------------------------

	/**
	 * Locates all PSR4 compatible namespaces from Composer.
	 * 从Composer中找到所有PSR4兼容的名称空间
	 */
	protected function discoverComposerNamespaces()
	{
		if (! is_file(COMPOSER_PATH))
		{
			return false;
		}

		$composer = include COMPOSER_PATH;

		$paths = $composer->getPrefixesPsr4();
		unset($composer);

		// Get rid of CodeIgniter so we don't have duplicates
		if (isset($paths['CodeIgniter\\']))
		{
			unset($paths['CodeIgniter\\']);
		}

		// Composer stores namespaces with trailing slash. We don't.
		// 编写器存储带有尾斜杠的名称空间。我们没有。
		$newPaths = [];
		foreach ($paths as $key => $value)
		{
			$newPaths[rtrim($key, '\\ ')] = $value;
		}

		$this->prefixes = array_merge($this->prefixes, $newPaths);
	}
}
