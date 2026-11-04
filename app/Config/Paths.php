<?php
/**
 * 配置，路径
 */

namespace Config;

/**
 * Paths
 * 路径
 *
 * Holds the paths that are used by the system to
 * locate the main directories, app, system, etc.
 * 包含系统用于定位主目录、应用程序、系统等的路径。
 *
 * Modifying these allows you to restructure your application,
 * share a system folder between multiple applications, and more.
 * 修改这些设置后，您可以重新组织应用程序、在多个应用之间共享系统文件夹等。
 *
 * All paths are relative to the project's root folder.
 * 所有路径都相对于项目的根文件夹。
 */

class Paths
{
	/**
	 * ---------------------------------------------------------------
	 * SYSTEM FOLDER NAME	系统目录名称
	 * ---------------------------------------------------------------
	 *
	 * This must contain the name of your "system" folder. Include
	 * the path if the folder is not in the same directory as this file.
	 * 这必须包含您“系统”文件夹的名称。如果文件夹不在本文件的同一目录中，请包含路径。
	 *
	 * @var string
	 */
	public $systemDirectory = __DIR__ . '/../../system';

	/**
	 * ---------------------------------------------------------------
	 * APPLICATION FOLDER NAME	应用路径
	 * ---------------------------------------------------------------
	 *
	 * If you want this front controller to use a different "app"
	 * folder than the default one you can set its name here. The folder
	 * can also be renamed or relocated anywhere on your getServer. If
	 * you do, use a full getServer path.
	 * 如果你想让这个前端控制器使用不同于默认的“应用”文件夹，可以在这里设置其名称。
	 * 该文件夹也可以在getServer上的任何地方重新命名或重新设置。如果您这样做,使用一个完整的getServer路径。
	 *
	 * @see http://codeigniter.com/user_guide/general/managing_apps.html
	 *
	 * @var string
	 */
	public $appDirectory = __DIR__ . '/..';

	/**
	 * ---------------------------------------------------------------
	 * WRITABLE DIRECTORY NAME	可写目录名
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of your "writable" directory.
	 * The writable directory allows you to group all directories that
	 * need write permission to a single place that can be tucked away
	 * for maximum security, keeping it out of the app and/or
	 * system directories.
	 * 这个变量必须包含“可写”目录的名称。
	 * writable目录允许您分组所有需要写权限的目录,这些目录可以隐藏在最大安全范围内,将其排除在应用程序和/或系统目录中。
	 *
	 * @var string
	 */
	public $writableDirectory = __DIR__ . '/../../writable';

	/**
	 * ---------------------------------------------------------------
	 * TESTS DIRECTORY NAME	测试目录名称
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of your "tests" directory.
	 * 该变量必须包含“测试”目录的名称
	 *
	 * @var string
	 */
	public $testsDirectory = __DIR__ . '/../../tests';

	/**
	 * ---------------------------------------------------------------
	 * VIEW DIRECTORY NAME	视图目录名称
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of the directory that
	 * contains the view files used by your application. By
	 * default this is in `app/Views`. This value
	 * is used when no value is provided to `Services::renderer()`.
	 * 这个变量必须包含包含应用程序使用的视图文件的目录的名称。默认情况下,这是在“应用程序/视图”中。
	 *
	 * @var string
	 */
	public $viewDirectory = __DIR__ . '/../Views';
}
