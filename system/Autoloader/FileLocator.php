<?php
/**
 * CodeIgniter，自动加载器，文件探测器
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

/**
 * Class FileLocator
 * 文件探测器类
 *
 * Allows loading non-class files in a namespaced manner.
 * Works with Helpers, Views, etc.
 * 支持以命名空间方式加载非类文件。适用于辅助功能、视图等。
 */
class FileLocator
{
	/**
	 * The Autoloader to use.
	 * 使用自动装填机
	 *
	 * @var \CodeIgniter\Autoloader\Autoloader
	 */
	protected $autoloader;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param Autoloader $autoloader
	 */
	public function __construct(Autoloader $autoloader)
	{
		$this->autoloader = $autoloader;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to locate a file by examining the name for a namespace
	 * and looking through the PSR-4 namespaced files that we know about.
	 * 通过检查命名空间的名称，并浏览我们已知的 PSR-4 命名空间文件，来尝试定位文件。
	 *
	 * @param string $file   The namespaced file to locate
	 * @param string $folder The folder within the namespace that we should look for the file.
	 * @param string $ext    The file extension the file should have.
	 *
	 * @return string|false The path to the file, or false if not found.
	 */
	public function locateFile(string $file, string $folder = null, string $ext = 'php')
	{
		$file = $this->ensureExt($file, $ext);

		// Clears the folder name if it is at the beginning of the filename
		// 如果文件夹名称位于文件名的开头，则清除该文件夹名称。
		if (! empty($folder) && ($pos = strpos($file, $folder)) === 0)
		{
			$file = substr($file, strlen($folder . '/'));
		}

		// Is not namespaced? Try the application folder.
		// 没有命名空间？试试应用程序文件夹。
		if (strpos($file, '\\') === false)
		{
			return $this->legacyLocate($file, $folder);
		}

		// Standardize slashes to handle nested directories.
		// 标准化斜杠以处理嵌套目录
		$file = strtr($file, '/', '\\');

		$segments = explode('\\', $file);

		// The first segment will be empty if a slash started the filename.
		// 如果文件名以斜杠开头，第一个段将为空。
		if (empty($segments[0]))
		{
			unset($segments[0]);
		}

		$paths    = [];
		$prefix   = '';
		$filename = '';

		// Namespaces always comes with arrays of paths
		// 命名空间总是带有路径数组
		$namespaces = $this->autoloader->getNamespace();

		while (! empty($segments))
		{
			$prefix .= empty($prefix) ? array_shift($segments) : '\\' . array_shift($segments);

			if (empty($namespaces[$prefix]))
			{
				continue;
			}
			$paths = $namespaces[$prefix];

			$filename = implode('/', $segments);
			break;
		}

		// if no namespaces matched then quit
		if (empty($paths))
		{
			return false;
		}

		// Check each path in the namespace
		foreach ($paths as $path)
		{
			// Ensure trailing slash
			$path = rtrim($path, '/') . '/';

			// If we have a folder name, then the calling function
			// expects this file to be within that folder, like 'Views',
			// or 'libraries'.
			if (! empty($folder) && strpos($path . $filename, '/' . $folder . '/') === false)
			{
				$path .= trim($folder, '/') . '/';
			}

			$path .= $filename;
			if (is_file($path))
			{
				return $path;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Examines a file and returns the fully qualified domain name.
	 * 检查文件并返回完全限定的域名
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function getClassname(string $file) : string
	{
		$php       = file_get_contents($file);
		$tokens    = token_get_all($php);
		$dlm       = false;
		$namespace = '';
		$className = '';

		foreach ($tokens as $i => $token)
		{
			if ($i < 2)
			{
				continue;
			}

			if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] === 'phpnamespace' || $tokens[$i - 2][1] === 'namespace')) || ($dlm && $tokens[$i - 1][0] === T_NS_SEPARATOR && $token[0] === T_STRING))
			{
				if (! $dlm)
				{
					$namespace = 0;
				}
				if (isset($token[1]))
				{
					$namespace = $namespace ? $namespace . '\\' . $token[1] : $token[1];
					$dlm       = true;
				}
			}
			elseif ($dlm && ($token[0] !== T_NS_SEPARATOR) && ($token[0] !== T_STRING))
			{
				$dlm = false;
			}
			if (($tokens[$i - 2][0] === T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] === 'phpclass'))
				&& $tokens[$i - 1][0] === T_WHITESPACE
				&& $token[0] === T_STRING)
			{
				$className = $token[1];
				break;
			}
		}

		if (empty($className))
		{
			return '';
		}

		return $namespace . '\\' . $className;
	}

	//--------------------------------------------------------------------

	/**
	 * Searches through all of the defined namespaces looking for a file.
	 * Returns an array of all found locations for the defined file.
	 * 遍历所有已定义的命名空间，查找指定文件。返回一个包含所有找到位置的数组。
	 *
	 * Example:
	 *
	 *  $locator->search('Config/Routes.php');
	 *  // Assuming PSR4 namespaces include foo and bar, might return:
	 *  [
	 *      'app/Modules/foo/Config/Routes.php',
	 *      'app/Modules/bar/Config/Routes.php',
	 *  ]
	 *
	 * @param string  $path
	 * @param string  $ext
	 * @param boolean $prioritizeApp
	 *
	 * @return array
	 */
	public function search(string $path, string $ext = 'php', bool $prioritizeApp = true): array
	{
		$path = $this->ensureExt($path, $ext);

		$foundPaths = [];
		$appPaths   = [];

		foreach ($this->getNamespaces() as $namespace)
		{
			if (isset($namespace['path']) && is_file($namespace['path'] . $path))
			{
				$fullPath = $namespace['path'] . $path;
				if ($prioritizeApp)
				{
					$foundPaths[] = $fullPath;
				}
				else
				{
					if (strpos($fullPath, APPPATH) === 0)
					{
						$appPaths[] = $fullPath;
					}
					else
					{
						$foundPaths[] = $fullPath;
					}
				}
			}
		}

		if (! $prioritizeApp && ! empty($appPaths))
		{
			$foundPaths = array_merge($foundPaths, $appPaths);
		}

		// Remove any duplicates
		$foundPaths = array_unique($foundPaths);

		return $foundPaths;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures a extension is at the end of a filename
	 * 确保扩展名位于文件名的末尾
	 *
	 * @param string $path
	 * @param string $ext
	 *
	 * @return string
	 */
	protected function ensureExt(string $path, string $ext): string
	{
		if ($ext)
		{
			$ext = '.' . $ext;

			if (substr($path, -strlen($ext)) !== $ext)
			{
				$path .= $ext;
			}
		}

		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Return the namespace mappings we know about.
	 * 返回已知的名称空间映射
	 *
	 * @return array|string
	 */
	protected function getNamespaces()
	{
		$namespaces = [];

		// Save system for last
		$system = [];

		foreach ($this->autoloader->getNamespace() as $prefix => $paths)
		{
			foreach ($paths as $path)
			{
				if ($prefix === 'CodeIgniter')
				{
					$system = [
						'prefix' => $prefix,
						'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,
					];

					continue;
				}

				$namespaces[] = [
					'prefix' => $prefix,
					'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,
				];
			}
		}

		$namespaces[] = $system;

		return $namespaces;
	}

	//--------------------------------------------------------------------

	/**
	 * Find the qualified name of a file according to
	 * the namespace of the first matched namespace path.
	 * 根据第一个匹配的命名空间路径的命名空间，查找文件的合格名称。
	 *
	 * @param string $path
	 *
	 * @return string|false The qualified name or false if the path is not found
	 */
	public function findQualifiedNameFromPath(string $path)
	{
		$path = realpath($path) ?: $path;

		if (! is_file($path))
		{
			return false;
		}

		foreach ($this->getNamespaces() as $namespace)
		{
			$namespace['path'] = realpath($namespace['path']) ?: $namespace['path'];

			if (empty($namespace['path']))
			{
				continue;
			}

			if (mb_strpos($path, $namespace['path']) === 0)
			{
				$className = '\\' . $namespace['prefix'] . '\\' .
						ltrim(str_replace('/', '\\', mb_substr(
							$path, mb_strlen($namespace['path']))
						), '\\');
				// Remove the file extension (.php)
				$className = mb_substr($className, 0, -4);

				// Check if this exists
				if (class_exists($className))
				{
					return $className;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the defined namespaces, returning a list of all files
	 * that are contained within the subpath specified by $path.
	 * 扫描指定的命名空间，返回 $path 指定子路径内所有文件的列表。
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function listFiles(string $path): array
	{
		if (empty($path))
		{
			return [];
		}

		$files = [];
		helper('filesystem');

		foreach ($this->getNamespaces() as $namespace)
		{
			$fullPath = $namespace['path'] . $path;
			$fullPath = realpath($fullPath) ?: $fullPath;

			if (! is_dir($fullPath))
			{
				continue;
			}

			$tempFiles = get_filenames($fullPath, true);

			if (! empty($tempFiles))
			{
				$files = array_merge($files, $tempFiles);
			}
		}

		return $files;
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the provided namespace, returning a list of all files
	 * that are contained within the subpath specified by $path.
	 * 扫描提供的命名空间，返回 $path 指定子路径内所有文件的列表。
	 *
	 * @param string $prefix
	 * @param string $path
	 *
	 * @return array
	 */
	public function listNamespaceFiles(string $prefix, string $path): array
	{
		if (empty($path) || empty($prefix))
		{
			return [];
		}

		$files = [];
		helper('filesystem');

		// autoloader->getNamespace($prefix) returns an array of paths for that namespace
		foreach ($this->autoloader->getNamespace($prefix) as $namespacePath)
		{
			$fullPath = rtrim($namespacePath, '/') . '/' . $path;
			$fullPath = realpath($fullPath) ?: $fullPath;

			if (! is_dir($fullPath))
			{
				continue;
			}

			$tempFiles = get_filenames($fullPath, true);

			if (! empty($tempFiles))
			{
				$files = array_merge($files, $tempFiles);
			}
		}

		return $files;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the app folder to see if the file can be found.
	 * Only for use with filenames that DO NOT include namespacing.
	 * 检查应用文件夹，查看文件是否可找到。仅适用于不包含命名空间的文件名。
	 *
	 * @param string      $file
	 * @param string|null $folder
	 *
	 * @return string|false The path to the file, or false if not found.
	 */
	protected function legacyLocate(string $file, string $folder = null)
	{
		$path = APPPATH . (empty($folder) ? $file : $folder . '/' . $file);
		$path = realpath($path) ?: $path;

		if (is_file($path))
		{
			return $path;
		}

		return false;
	}
}
