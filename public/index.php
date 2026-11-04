<?php
/**
 * index，默认入口文件
 */

// Valid PHP Version?
// 验证PHP版本
$minPHPVersion = '7.2';
if (version_compare(PHP_VERSION, $minPHPVersion, '<'))
{
	die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . PHP_VERSION);
}
unset($minPHPVersion);

// Path to the front controller (this file)
// 到前端控制器的路径（此文件）
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION	引导应用程序
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 * 该过程会设置路径常量，加载并注册我们的自动加载器以及 Composer 的自动加载器，
 * 加载常量，并启动特定环境的初始化流程。
 */

// Ensure the current directory is pointing to the front controller's directory
// 确保当前目录指向前端控制器的目录
chdir(__DIR__);

// Load our paths config file
// This is the line that might need to be changed, depending on your folder structure.
// 加载我们的路径配置文件。这行代码可能需要根据您的文件夹结构进行修改。
require realpath(FCPATH . '../app/Config/Paths.php') ?: FCPATH . '../app/Config/Paths.php';
// ^^^ Change this if you move your application folder

$paths = new Config\Paths();

// Location of the framework bootstrap file.
// 框架引导文件的位置
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app       = require realpath($bootstrap) ?: $bootstrap;

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION	启动应用程序
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do its thang.
 * 现在一切设置好了，是时候真正启动引擎，让这个应用开始发挥作用了。
 */
$app->run();
