<?php
/**
 * 测试，支持，配置，服务
 */

namespace Tests\Support\Config;

use CodeIgniter\HTTP\URI;
use Config\Services as BaseServices;
use RuntimeException;

/**
 * Services Class
 * 服务类。
 *
 * Provides a replacement uri Service
 * to demonstrate overriding core services.
 * 提供一个替代URI服务，用于演示覆盖核心服务。
 */
class Services extends BaseServices
{
	/**
	 * The URI class provides a way to model and manipulate URIs.
	 * URI类提供了一种对URI建模和操作的方法
	 *
	 * @param string  $uri
	 * @param boolean $getShared
	 *
	 * @return URI
	 */
	public static function uri(string $uri = null, bool $getShared = true)
	{
		// Intercept our test case
		if ($uri === 'testCanReplaceFrameworkServices')
		{
			throw new RuntimeException('Service originated from ' . static::class);
		}

		if ($getShared)
		{
			return static::getSharedInstance('uri', $uri);
		}

		return new URI($uri);
	}
}
