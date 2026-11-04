<?php
/**
 * CodeIgniter，模块，Modules
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Modules;

/**
 * Modules Class
 * 模块类。
 *
 * @link https://codeigniter.com/user_guide/general/modules.html
 */
class Modules
{
	/**
	 * Auto-Discover
	 * 自动探测
	 *
	 * @var boolean
	 */
	public $enabled = true;

	/**
	 * Auto-Discovery Within Composer Packages
	 * 自动发现Composer包
	 *
	 * @var boolean
	 */
	public $discoverInComposer = true;

	/**
	 * Auto-Discover Rules Handler
	 * 自动发现规则处理程序
	 *
	 * @var array
	 */
	public $aliases = [];

	/**
	 * Should the application auto-discover the requested resource.
	 * 如果应用程序自动发现所请求的资源
	 *
	 * @param string $alias
	 *
	 * @return boolean
	 */
	public function shouldDiscover(string $alias): bool
	{
		if (! $this->enabled)
		{
			return false;
		}

		return in_array(strtolower($alias), $this->aliases, true);
	}
}
