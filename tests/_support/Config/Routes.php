<?php namespace Tests\Support\Config;

/**
 * 测试，支持，配置，路由
 */

/**
 * This is a simple file to include for testing the RouteCollection class.
 * 这是一个用于测试RouteCollection类的简单文件。
 */
$routes->add('testing', 'TestController::index', ['as' => 'testing-index']);
