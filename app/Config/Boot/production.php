<?php
/**
 * 配置，启动，生产模式
 */

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY	错误显示
 |--------------------------------------------------------------------------
 | Don't show ANY in production environments. Instead, let the system catch
 | it and display a generic error message.
 | 在生产环境中不要显示任何内容。而是让系统自动捕获并显示通用的错误信息
 */
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE	调试模式
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. It's not widely used currently, and may not survive
 | release of the framework.
 | 调试模式是一个实验性标志，可允许对整个系统进行修改。
 | 目前尚未广泛使用，且可能在框架发布后不再保留。
 */
defined('CI_DEBUG') || define('CI_DEBUG', false);
