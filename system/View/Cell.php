<?php
/**
 * CodeIgniter，视图，单元格
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\View\Exceptions\ViewException;
use Config\Services;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Cell
 * 单元格类。
 *
 * A simple class that can call any other class that can be loaded,
 * and echo out it's result. Intended for displaying small blocks of
 * content within views that can be managed by other libraries and
 * not require they are loaded within controller.
 * 一个简单的类，可以调用任何可加载的其他类，并输出其结果。
 * 用于在视图中显示小块内容，这些内容可由其他库进行管理，而无需在控制器中加载。
 *
 * Used with the helper function, it's use will look like:
 *
 *         viewCell('\Some\Class::method', 'limit=5 sort=asc', 60, 'cache-name');
 *
 * Parameters are matched up with the callback method's arguments of the same name:
 *
 *         class Class {
 *             function method($limit, $sort)
 *         }
 *
 * Alternatively, the params will be passed into the callback method as a simple array
 * if matching params are not found.
 *
 *         class Class {
 *             function method(array $params=null)
 *         }
 */
class Cell
{
	/**
	 * Instance of the current Cache Instance
	 * 当前缓存实例的实例
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	//--------------------------------------------------------------------

	/**
	 * Cell constructor.
	 * 单元格构造函数
	 *
	 * @param CacheInterface $cache
	 */
	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	//--------------------------------------------------------------------

	/**
	 * Render a cell, returning its body as a string.
	 * 渲染一个单元格，将其主体作为字符串返回。
	 *
	 * @param string      $library
	 * @param null        $params
	 * @param integer     $ttl
	 * @param string|null $cacheName
	 *
	 * @return string
	 * @throws ReflectionException
	 */
	public function render(string $library, $params = null, int $ttl = 0, string $cacheName = null): string
	{
		list($class, $method) = $this->determineClass($library);

		// Is it cached?
		$cacheName = ! empty($cacheName)
			? $cacheName
			: str_replace(['\\', '/'], '', $class) . $method . md5(serialize($params));

		if (! empty($this->cache) && $output = $this->cache->get($cacheName))
		{
			return $output;
		}

		// Not cached - so grab it...
		$instance = new $class();

		if (method_exists($instance, 'initController'))
		{
			$instance->initController(Services::request(), Services::response(), Services::logger());
		}

		if (! method_exists($instance, $method))
		{
			throw ViewException::forInvalidCellMethod($class, $method);
		}

		// Try to match up the parameter list we were provided
		// with the parameter name in the callback method.
		// 尝试将我们收到的参数列表与回调方法中的参数名称进行匹配
		$paramArray = $this->prepareParams($params);
		$refMethod  = new ReflectionMethod($instance, $method);
		$paramCount = $refMethod->getNumberOfParameters();
		$refParams  = $refMethod->getParameters();

		if ($paramCount === 0)
		{
			if (! empty($paramArray))
			{
				throw ViewException::forMissingCellParameters($class, $method);
			}

			$output = $instance->{$method}();
		}
		elseif (($paramCount === 1)
			&& ((! array_key_exists($refParams[0]->name, $paramArray))
			|| (array_key_exists($refParams[0]->name, $paramArray)
			&& count($paramArray) !== 1))
		)
		{
			$output = $instance->{$method}($paramArray);
		}
		else
		{
			$fireArgs     = [];
			$methodParams = [];

			foreach ($refParams as $arg)
			{
				$methodParams[$arg->name] = true;
				if (array_key_exists($arg->name, $paramArray))
				{
					$fireArgs[$arg->name] = $paramArray[$arg->name];
				}
			}

			foreach ($paramArray as $key => $val)
			{
				if (! isset($methodParams[$key]))
				{
					throw ViewException::forInvalidCellParameter($key);
				}
			}

			$output = $instance->$method(...array_values($fireArgs));
		}
		// Can we cache it?
		if (! empty($this->cache) && $ttl !== 0)
		{
			$this->cache->save($cacheName, $output, $ttl);
		}
		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the params attribute. If an array, returns untouched.
	 * If a string, it should be in the format "key1=value key2=value".
	 * It will be split and returned as an array.
	 * 解析params属性。如果是数组，则返回原封不动。
	 *
	 * @param mixed $params
	 *
	 * @return array|null
	 */
	public function prepareParams($params)
	{
		if (empty($params) || (! is_string($params) && ! is_array($params)))
		{
			return [];
		}

		if (is_string($params))
		{
			$newParams = [];
			$separator = ' ';

			if (strpos($params, ',') !== false)
			{
				$separator = ',';
			}

			$params = explode($separator, $params);
			unset($separator);

			foreach ($params as $p)
			{
				if (! empty($p))
				{
					list($key, $val)       = explode('=', $p);
					$newParams[trim($key)] = trim($val, ', ');
				}
			}

			$params = $newParams;

			unset($newParams);
		}

		if (is_array($params) && empty($params))
		{
			return [];
		}

		return $params;
	}

	//--------------------------------------------------------------------

	/**
	 * Given the library string, attempts to determine the class and method
	 * to call.
	 * 根据库字符串，尝试确定要调用的类和方法。
	 *
	 * @param string $library
	 *
	 * @return array
	 */
	protected function determineClass(string $library): array
	{
		// We don't want to actually call static methods
		// by default, so convert any double colons.
		// 我们默认不希望调用静态方法，因此将任何双冒号进行转换。
		$library = str_replace('::', ':', $library);

		list($class, $method) = explode(':', $library);

		if (empty($class))
		{
			throw ViewException::forNoCellClass();
		}

		if (! class_exists($class, true))
		{
			throw ViewException::forInvalidCellClass($class);
		}

		if (empty($method))
		{
			$method = 'index';
		}

		return [
			$class,
			$method,
		];
	}

	//--------------------------------------------------------------------
}
