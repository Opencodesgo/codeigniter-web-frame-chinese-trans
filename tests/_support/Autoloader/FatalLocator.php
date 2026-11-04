<?php
/**
 * 测试，支持，自动加载器，Fatal 定位器 
 */

namespace Tests\Support\Autoloader;

use CodeIgniter\Autoloader\FileLocator;
use RuntimeException;

/**
 * Class FatalLocator
 * Fatal 定位器类。
 *
 * A locator replacement designed to throw
 * exceptions when used to indicate when
 * a lookup actually happens.
 * 一个用于在查找实际发生时抛出异常的定位器替换。
 */
class FatalLocator extends FileLocator
{

	/**
	 * Throws.
	 * 投掷
	 *
	 * @param string $file   The namespaced file to locate
	 * @param string $folder The folder within the namespace that we should look for the file.
	 * @param string $ext    The file extension the file should have.
	 *
	 * @return string|false The path to the file, or false if not found.
	 */
	public function locateFile(string $file, string $folder = null, string $ext = 'php')
	{
		$folder = $folder ?? 'null';
		throw new RuntimeException("locateFile({$file}, {$folder}, {$ext})");
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
		$prioritizeApp = $prioritizeApp ? 'true' : 'false';
		throw new RuntimeException("search({$path}, {$ext}, {$prioritizeApp})");
	}
}
