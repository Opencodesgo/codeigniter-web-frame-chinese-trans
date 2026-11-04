<?php
/**
 * 配置，路由
 */

namespace Config;

// Create a new instance of our RouteCollection class.
// 创建一个RouteCollection类的新实例
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
// 先加载系统的路由文件，以便应用程序和环境可以根据需要进行覆盖。
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup	路由器设置
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions	路由定义
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// 由于无需扫描目录，指定默认路由可提高性能。
$routes->get('/', 'Home::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing	附加路由
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 * 有时您需要额外的路由设置，并且需要能够覆盖此文件中的任何默认配置。
 * 基于环境的路由就是其中之一。
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 * 您将可以在不重新加载的情况下访问该文件中的 $routes 对象。
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
