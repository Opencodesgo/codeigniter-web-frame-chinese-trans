<?php
/**
 * 配置，事件
 */

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events	应用事件
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 * 事件使您能够在不修改或扩展核心文件的情况下，直接调用程序的执行功能。
 * 这个文件提供了一个中心位置来定义您的事件,尽管它们总是可以在运行时添加,如果需要的话也可以在运行时添加。
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', function () {
	if (ENVIRONMENT !== 'testing')
	{
		if (ini_get('zlib.output_compression'))
		{
			throw FrameworkException::forEnabledZlibOutputCompression();
		}

		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		ob_start(function ($buffer) {
			return $buffer;
		});
	}

	/*
	 * --------------------------------------------------------------------
	 * Debug Toolbar Listeners.	调试工具栏侦听器
	 * --------------------------------------------------------------------
	 * If you delete, they will no longer be collected.
	 * 如果删除，它们将不再被收集。
	 */
	if (CI_DEBUG)
	{
		Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
		Services::toolbar()->respond();
	}
});
