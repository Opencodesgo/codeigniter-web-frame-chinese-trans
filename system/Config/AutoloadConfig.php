<?php
/**
 * CodeIgniter，配置，自动装载配置
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

/**
 * AUTOLOADER
 * 自动装载配置
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 * 该文件定义了命名空间和类映射，以便自动加载器在需要时能够找到相应的文件。
 */
class AutoloadConfig
{
	/**
	 * -------------------------------------------------------------------
	 * Namespaces	命名空间
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 * 该映射将应用程序中任何命名空间的位置与其在文件系统中的位置相对应。
	 * 这些信息由自动加载器用于首次实例化时定位文件。
	 *
	 * The '/app' and '/system' directories are already mapped for you.
	 * you may change the name of the 'App' namespace if you wish,
	 * but this should be done prior to creating any namespaced classes,
	 * else you will need to modify all of those classes for this to work.
	 *
	 * @var array
	 */
	public $psr4 = [];

	/**
	 * -------------------------------------------------------------------
	 * Class Map	类映射
	 * -------------------------------------------------------------------
	 * The class map provides a map of class names and their exact
	 * location on the drive. Classes loaded in this manner will have
	 * slightly faster performance because they will not have to be
	 * searched for within one or more directories as they would if they
	 * were being autoloaded through a namespace.
	 * 类图提供了类名及其在驱动器上确切位置的地图。
	 *
	 * @var array
	 */
	public $classmap = [];

	/**
	 * -------------------------------------------------------------------
	 * Namespaces	命名空间
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 * 该映射将应用程序中任何命名空间的位置与其在文件系统中的位置相对应。
	 * 这些信息由自动加载器用于首次实例化时定位文件。
	 *
	 * Do not change the name of the CodeIgniter namespace or your application
	 * will break.
	 *
	 * @var array
	 */
	protected $corePsr4 = [
		'CodeIgniter' => SYSTEMPATH,
		'App'         => APPPATH, // To ensure filters, etc still found,
	];

	/**
	 * -------------------------------------------------------------------
	 * Class Map	类映射
	 * -------------------------------------------------------------------
	 * The class map provides a map of class names and their exact
	 * location on the drive. Classes loaded in this manner will have
	 * slightly faster performance because they will not have to be
	 * searched for within one or more directories as they would if they
	 * were being autoloaded through a namespace.
	 * 类图提供了类名及其在驱动器上确切位置的地图。
	 * 以这种方式加载的类性能会稍快一些，因为它们无需像通过命名空间自动加载时那样，在一个或多个目录中进行搜索。
	 *
	 * @var array
	 */
	protected $coreClassmap = [
		'Psr\Log\AbstractLogger'           => SYSTEMPATH . 'ThirdParty/PSR/Log/AbstractLogger.php',
		'Psr\Log\InvalidArgumentException' => SYSTEMPATH . 'ThirdParty/PSR/Log/InvalidArgumentException.php',
		'Psr\Log\LoggerAwareInterface'     => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareInterface.php',
		'Psr\Log\LoggerAwareTrait'         => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareTrait.php',
		'Psr\Log\LoggerInterface'          => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerInterface.php',
		'Psr\Log\LoggerTrait'              => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerTrait.php',
		'Psr\Log\LogLevel'                 => SYSTEMPATH . 'ThirdParty/PSR/Log/LogLevel.php',
		'Psr\Log\NullLogger'               => SYSTEMPATH . 'ThirdParty/PSR/Log/NullLogger.php',
		'Laminas\Escaper\Escaper'          => SYSTEMPATH . 'ThirdParty/Escaper/Escaper.php',
	];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数。
	 *
	 * Merge the built-in and developer-configured psr4 and classmap,
	 * with preference to the developer ones.
	 * 合并内置和开发者配置的 psr4 及类映射，优先使用开发者配置的。
	 */
	public function __construct()
	{
		if (isset($_SERVER['CI_ENVIRONMENT']) && $_SERVER['CI_ENVIRONMENT'] === 'testing')
		{
			$this->psr4['Tests\Support']                  = SUPPORTPATH;
			$this->classmap['CodeIgniter\Log\TestLogger'] = SYSTEMPATH . 'Test/TestLogger.php';
			$this->classmap['CIDatabaseTestCase']         = SYSTEMPATH . 'Test/CIDatabaseTestCase.php';
		}

		$this->psr4     = array_merge($this->corePsr4, $this->psr4);
		$this->classmap = array_merge($this->coreClassmap, $this->classmap);
	}
}
