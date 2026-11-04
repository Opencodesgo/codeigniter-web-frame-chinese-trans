<?php
/**
 * 配置，Kint
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Kint\Renderer\Renderer;

/**
 * --------------------------------------------------------------------------
 * Kint	Kint
 * --------------------------------------------------------------------------
 *
 * We use Kint's `RichRenderer` and `CLIRenderer`. This area contains options
 * that you can set to customize how Kint works for you.
 * 我们使用 Kint 的 `RichRenderer` 和 `CLIRenderer`。此区域包含可设置的选项，用于自定义 Kint 为您提供的功能。
 *
 * @see https://kint-php.github.io/kint/ for details on these settings.
 */
class Kint extends BaseConfig
{
	/*
	|--------------------------------------------------------------------------
	| Global Settings	全局设置
	|--------------------------------------------------------------------------
	*/

	public $plugins = null;

	public $maxDepth = 6;

	public $displayCalledFrom = true;

	public $expanded = false;

	/*
	|--------------------------------------------------------------------------
	| RichRenderer Settings		RichRenderer设置
	|--------------------------------------------------------------------------
	*/
	public $richTheme = 'aante-light.css';

	public $richFolder = false;

	public $richSort = Renderer::SORT_FULL;

	public $richObjectPlugins = null;

	public $richTabPlugins = null;

	/*
	|--------------------------------------------------------------------------
	| CLI Settings	CLI设置
	|--------------------------------------------------------------------------
	*/
	public $cliColors = true;

	public $cliForceUTF8 = false;

	public $cliDetectWidth = true;

	public $cliMinWidth = 40;
}
