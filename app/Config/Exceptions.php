<?php
/**
 * 配置，异常
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Setup how the exception handler works.
 * 设置异常处理程序的工作方式。
 */
class Exceptions extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * LOG EXCEPTIONS?	日志异常？
	 * --------------------------------------------------------------------------
	 * If true, then exceptions will be logged
	 * through Services::Log.
	 * 如果为真，则异常将通过 Services::Log 记录。
	 *
	 * Default: true
	 *
	 * @var boolean
	 */
	public $log = true;

	/**
	 * --------------------------------------------------------------------------
	 * DO NOT LOG STATUS CODES	不记录状态码
	 * --------------------------------------------------------------------------
	 * Any status codes here will NOT be logged if logging is turned on.
	 * By default, only 404 (Page Not Found) exceptions are ignored.
	 * 如果打开了日志记录，这里的任何状态码都不会被记录。
	 * 默认情况下,只忽略了404(没有发现的页面)。
	 *
	 * @var array
	 */
	public $ignoreCodes = [404];

	/**
	 * --------------------------------------------------------------------------
	 * Error Views Path	错误视图路径
	 * --------------------------------------------------------------------------
	 * This is the path to the directory that contains the 'cli' and 'html'
	 * directories that hold the views used to generate errors.
	 * 这是包含“cli”和“html”目录的路径，这些目录中存放了用于生成错误的视图。
	 *
	 * Default: APPPATH.'Views/errors'
	 *
	 * @var string
	 */
	public $errorViewPath = APPPATH . 'Views/errors';
}
