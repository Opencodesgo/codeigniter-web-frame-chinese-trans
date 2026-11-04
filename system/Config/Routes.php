<?php
/**
 * CodeIgniter，配置，路由
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * System URI Routing
 * 系统URI路由。
 *
 * This file contains any routing to system tools, such as command-line
 * tools for migrations, etc.
 * 此文件包含任何指向系统工具的路由，例如迁移命令行工具等。
 *
 * It is called by Config\Routes, and has the $routes RouteCollection
 * already loaded up and ready for us to use.
 * 它由 Config\Routes 调用，已经加载了 $routes RouteCollection，可供我们直接使用。
 */

// Prevent access to BaseController
$routes->add('BaseController(:any)', function () {
	throw PageNotFoundException::forPageNotFound();
});

// Prevent access to initController method
$routes->add('(:any)/initController', function () {
	throw PageNotFoundException::forPageNotFound();
});

// Migrations
$routes->cli('migrations/(:segment)/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1/$2');
$routes->cli('migrations/(:segment)', '\CodeIgniter\Commands\MigrationsCommand::$1');
$routes->cli('migrations', '\CodeIgniter\Commands\MigrationsCommand::index');

// CLI Catchall - uses a _remap to call Commands
$routes->cli('ci(:any)', '\CodeIgniter\CLI\CommandRunner::index/$1');
