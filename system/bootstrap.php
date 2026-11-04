<?php
/**
 * CodeIgniter，引导
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\DotEnv;
use Config\App;
use Config\Autoload;
use Config\Modules;
use Config\Services;

/*
 * ---------------------------------------------------------------
 * SETUP OUR PATH CONSTANTS	设置路径常量
 * ---------------------------------------------------------------
 *
 * The path constants provide convenient access to the folders
 * throughout the application. We have to setup them up here
 * so they are available in the config files that are loaded.
 * 路径常量可方便地访问应用程序中的各个文件夹。
 * 我们必须在这里设置它们，以便在加载的配置文件中可用。
 */

// The path to the application directory.
// 应用程序目录的路径
if (! defined('APPPATH'))
{
	/**
	 * @var \Config\Paths $paths
	 */
	define('APPPATH', realpath(rtrim($paths->appDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

// The path to the project root directory. Just above APPPATH.
// 项目根目录的路径。就在APPPATH上面。
if (! defined('ROOTPATH'))
{
	define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
}

// The path to the system directory.
// 系统目录的路径
if (! defined('SYSTEMPATH'))
{
	/**
	 * @var \Config\Paths $paths
	 */
	define('SYSTEMPATH', realpath(rtrim($paths->systemDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

// The path to the writable directory.
// 可写目录的路径
if (! defined('WRITEPATH'))
{
	/**
	 * @var \Config\Paths $paths
	 */
	define('WRITEPATH', realpath(rtrim($paths->writableDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

// The path to the tests directory
// 测试目录的路径
if (! defined('TESTPATH'))
{
	/**
	 * @var \Config\Paths $paths
	 */
	define('TESTPATH', realpath(rtrim($paths->testsDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CONSTANTS & COMMON	攫取我们的常数
 * ---------------------------------------------------------------
 */
if (! defined('APP_NAMESPACE'))
{
	require_once APPPATH . 'Config/Constants.php';
}

// Require app/Common.php file if exists.
if (is_file(APPPATH . 'Common.php'))
{
	require_once APPPATH . 'Common.php';
}

// Require system/Common.php
require_once SYSTEMPATH . 'Common.php';

/*
 * ---------------------------------------------------------------
 * LOAD OUR AUTOLOADER	装上AUTOLOADER
 * ---------------------------------------------------------------
 *
 * The autoloader allows all of the pieces to work together in the
 * framework. We have to load it here, though, so that the config
 * files can use the path constants.
 * 自动加载器使所有组件能够在框架中协同工作。
 * 不过，我们必须在这里加载它，以便配置文件可以使用路径常量。
 */

if (! class_exists('Config\Autoload', false))
{
	require_once SYSTEMPATH . 'Config/AutoloadConfig.php';
	require_once APPPATH . 'Config/Autoload.php';
	require_once SYSTEMPATH . 'Modules/Modules.php';
	require_once APPPATH . 'Config/Modules.php';
}

require_once SYSTEMPATH . 'Autoloader/Autoloader.php';
require_once SYSTEMPATH . 'Config/BaseService.php';
require_once SYSTEMPATH . 'Config/Services.php';
require_once APPPATH . 'Config/Services.php';

// Use Config\Services as CodeIgniter\Services
if (! class_exists('CodeIgniter\Services', false))
{
	class_alias('Config\Services', 'CodeIgniter\Services');
}

// Initialize and register the loader with the SPL autoloader stack.
// 用SPL自动加载程序堆栈初始化并注册加载程序
Services::autoloader()->initialize(new Autoload(), new Modules())->register();

// Now load Composer's if it's available
// 如果Composer可用，现在加载它。
if (is_file(COMPOSER_PATH))
{
	/*
	 * The path to the vendor directory.
	 * 供应商目录的路径。
	 *
	 * We do not want to enforce this, so set the constant if Composer was used.
	 * 我们不想强制这样做，所以如果使用了Composer，就设置常量。
	 */
	if (! defined('VENDORPATH'))
	{
		define('VENDORPATH', realpath(ROOTPATH . 'vendor') . DIRECTORY_SEPARATOR);
	}

	require_once COMPOSER_PATH;
}

// Load environment settings from .env files into $_SERVER and $_ENV
// 加载环境设置.env文件到$ _SERVER和$ _ENV
require_once SYSTEMPATH . 'Config/DotEnv.php';

$env = new DotEnv(ROOTPATH);
$env->load();

// Always load the URL helper, it should be used in most of apps.
helper('url');

/*
 * ---------------------------------------------------------------
 * GRAB OUR CODEIGNITER INSTANCE	抓住我们的codeigniter实例
 * ---------------------------------------------------------------
 *
 * The CodeIgniter class contains the core functionality to make
 * the application run, and does all of the dirty work to get
 * the pieces all working together.
 * CodeIgniter 类包含了使应用程序运行的核心功能，负责处理所有繁琐的工作，以确保各个组件能够协同工作。
 */

$app = new CodeIgniter(new App());
$app->initialize();

return $app;
