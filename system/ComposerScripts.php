<?php
/**
 * CodeIgniter，Composer 脚本
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter;

use ReflectionClass;
use ReflectionException;

/**
 * ComposerScripts
 * Composer 脚本。
 *
 * These scripts are used by Composer during installs and updates
 * to move files to locations within the system folder so that end-users
 * do not need to use Composer to install a package, but can simply
 * download
 * 这些脚本在 Composer 安装和更新过程中被使用，用于将文件移动到系统文件夹中的指定位置，
 * 以便最终用户无需使用 Composer 安装包，只需直接下载即可。
 *
 * @codeCoverageIgnore
 */
class ComposerScripts
{
	/**
	 * Base path to use.
	 * 使用的基本路径
	 *
	 * @var string
	 */
	protected static $basePath = 'ThirdParty/';

	/**
	 * After composer install/update, this is called to move
	 * the bare-minimum required files for our dependencies
	 * to appropriate locations.
	 * 在安装或更新 composer 后，此命令会将依赖所需的最低限度文件移动到正确的位置。
	 *
	 * @throws ReflectionException
	 */
	public static function postUpdate()
	{
		static::moveEscaper();
		static::moveKint();
	}

	//--------------------------------------------------------------------

	/**
	 * Move a file.
	 * 移动文件
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return boolean
	 */
	protected static function moveFile(string $source, string $destination): bool
	{
		$source = realpath($source);

		if (empty($source))
		{
			// @codeCoverageIgnoreStart
			die('Cannot move file. Source path invalid.');
			// @codeCoverageIgnoreEnd
		}

		if (! is_file($source))
		{
			return false;
		}

		return copy($source, $destination);
	}

	//--------------------------------------------------------------------

	/**
	 * Determine file path of a class.
	 * 确定类的文件路径
	 *
	 * @param string $class
	 *
	 * @return string
	 * @throws ReflectionException
	 */
	protected static function getClassFilePath(string $class)
	{
		$reflector = new ReflectionClass($class);

		return $reflector->getFileName();
	}

	//--------------------------------------------------------------------

	/**
	 * A recursive remove directory method.
	 * 递归删除目录方法
	 *
	 * @param string $dir
	 */
	protected static function removeDir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);
			foreach ($objects as $object)
			{
				if ($object !== '.' && $object !== '..')
				{
					if (filetype($dir . '/' . $object) === 'dir')
					{
						static::removeDir($dir . '/' . $object);
					}
					else
					{
						unlink($dir . '/' . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	protected static function copyDir($source, $dest)
	{
		$dir = opendir($source);
		@mkdir($dest);

		while (false !== ($file = readdir($dir)))
		{
			if (($file !== '.') && ($file !== '..'))
			{
				if (is_dir($source . '/' . $file))
				{
					static::copyDir($source . '/' . $file, $dest . '/' . $file);
				}
				else
				{
					copy($source . '/' . $file, $dest . '/' . $file);
				}
			}
		}

		closedir($dir);
	}

	/**
	 * Moves the Laminas Escaper files into our base repo so that it's
	 * available for packaged releases where the users don't user Composer.
	 * 将 Laminas Escaper 文件移动到我们的主仓库，以便在用户不使用 Composer 的情况下，可用于打包发布。
	 *
	 * @throws ReflectionException
	 */
	public static function moveEscaper()
	{
		if (class_exists('\\Laminas\\Escaper\\Escaper') && is_file(static::getClassFilePath('\\Laminas\\Escaper\\Escaper')))
		{
			$base = basename(__DIR__) . '/' . static::$basePath . 'Escaper';

			foreach ([$base, $base . '/Exception'] as $path)
			{
				if (! is_dir($path))
				{
					mkdir($path, 0755);
				}
			}

			$files = [
				static::getClassFilePath('\\Laminas\\Escaper\\Exception\\ExceptionInterface')       => $base . '/Exception/ExceptionInterface.php',
				static::getClassFilePath('\\Laminas\\Escaper\\Exception\\InvalidArgumentException') => $base . '/Exception/InvalidArgumentException.php',
				static::getClassFilePath('\\Laminas\\Escaper\\Exception\\RuntimeException')         => $base . '/Exception/RuntimeException.php',
				static::getClassFilePath('\\Laminas\\Escaper\\Escaper')                             => $base . '/Escaper.php',
			];

			foreach ($files as $source => $dest)
			{
				if (! static::moveFile($source, $dest))
				{
					// @codeCoverageIgnoreStart
					die('Error moving: ' . $source);
					// @codeCoverageIgnoreEnd
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Moves the Kint file into our base repo so that it's
	 * available for packaged releases where the users don't user Composer.
	 * 将 Kint 文件移动到我们的主仓库，以便在用户不使用 Composer 的情况下，可用于打包发布。
	 */
	public static function moveKint()
	{
		$dir = 'vendor/kint-php/kint/src';

		if (is_dir($dir))
		{
			$base = basename(__DIR__) . '/' . static::$basePath . 'Kint';

			// Remove the contents of the previous Kint folder, if any.
			if (is_dir($base))
			{
				static::removeDir($base);
			}

			// Create Kint if it doesn't exist already
			// 如果Kint还不存在，创建它。
			if (! is_dir($base))
			{
				mkdir($base, 0755);
			}

			static::copyDir($dir, $base);
			static::copyDir($dir . '/../resources', $base . '/resources');
			copy($dir . '/../init.php', $base . '/init.php');
			copy($dir . '/../init_helpers.php', $base . '/init_helpers.php');
		}
	}
}
