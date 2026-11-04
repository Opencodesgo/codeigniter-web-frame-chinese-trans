<?php
/**
 * 配置，图像
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Images\Handlers\GDHandler;
use CodeIgniter\Images\Handlers\ImageMagickHandler;

class Images extends BaseConfig
{
	/**
	 * Default handler used if no other handler is specified.
	 * 如果没有指定其他处理程序，则使用默认处理程序。
	 *
	 * @var string
	 */
	public $defaultHandler = 'gd';

	/**
	 * The path to the image library.
	 * Required for ImageMagick, GraphicsMagick, or NetPBM.
	 * 图像库的路径。ImageMagick、GraphicsMagick或NetPBM必需。
	 *
	 * @var string
	 */
	public $libraryPath = '/usr/local/bin/convert';

	/**
	 * The available handler classes.
	 * 可用的处理程序类
	 * @var array<string, string>
	 */
	public $handlers = [
		'gd'      => GDHandler::class,
		'imagick' => ImageMagickHandler::class,
	];
}
