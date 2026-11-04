<?php
/**
 * 配置，启动，开发模式
 */

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY	错误显示
 |--------------------------------------------------------------------------
 | In development, we want to show as many errors as possible to help
 | make sure they don't make it to production. And save us hours of
 | painful debugging.
 | 在开发过程中，我们希望尽可能多地展示错误，以确保它们不会进入生产环境，并为我们节省数小时的痛苦调试时间。
 */
error_reporting(-1);
ini_set('display_errors', '1');

/*
 |--------------------------------------------------------------------------
 | DEBUG BACKTRACES		调试回溯
 |--------------------------------------------------------------------------
 | If true, this constant will tell the error screens to display debug
 | backtraces along with the other error information. If you would
 | prefer to not see this, set this value to false.
 | 如果为真，该常量将指示错误屏幕在显示其他错误信息的同时，也显示调试回溯信息。
 | 如果您不希望看到此内容，请将此值设置为 false。
 */
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE	调试模式
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. This will control whether Kint is loaded, and a few other
 | items. It can always be used within your own application too.
 | 调试模式是一个实验性标志，可允许对整个系统进行修改。它将控制Kint是否被加载，以及其他一些项目。
 | 你也可以在自己的应用程序中随时使用该功能。
 */
defined('CI_DEBUG') || define('CI_DEBUG', true);
