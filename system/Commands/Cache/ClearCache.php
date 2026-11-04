<?php
/**
 * CodeIgniter，命令，缓存，清除缓存
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Cache;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Clears current cache.
 * 清除当前缓存。
 */
class ClearCache extends BaseCommand
{
	/**
	 * Command grouping.
	 * 命令分组
	 *
	 * @var string
	 */
	protected $group = 'Cache';

	/**
	 * The Command's name
	 * 命令名称
	 *
	 * @var string
	 */
	protected $name = 'cache:clear';

	/**
	 * the Command's short description
	 * 命令简短描述
	 *
	 * @var string
	 */
	protected $description = 'Clears the current system caches.';

	/**
	 * the Command's usage
	 * 命令用法
	 *
	 * @var string
	 */
	protected $usage = 'cache:clear [driver]';

	/**
	 * the Command's Arguments
	 * 命令参数
	 *
	 * @var array
	 */
	protected $arguments = [
		'driver' => 'The cache driver to use',
	];

	/**
	 * Clears the cache
	 * 清除缓存
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$config  = config('Cache');
		$handler = $params[0] ?? $config->handler;

		if (! array_key_exists($handler, $config->validHandlers))
		{
			CLI::error($handler . ' is not a valid cache handler.');

			return;
		}

		$config->handler = $handler;
		$cache           = CacheFactory::getHandler($config);

		if (! $cache->clean())
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error while clearing the cache.');

			return;
			// @codeCoverageIgnoreEnd
		}

		CLI::write(CLI::color('Cache cleared.', 'green'));
	}
}
