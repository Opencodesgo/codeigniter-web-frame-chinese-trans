<?php
/**
 * CodeIgniter，公共
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\TestLogger;
use Config\App;
use Config\Database;
use Config\Logger;
use Config\Services;
use Config\View;
use Laminas\Escaper\Escaper;

//--------------------------------------------------------------------
// Services Convenience Functions	服务便利功能
//--------------------------------------------------------------------

if (! function_exists('app_timezone'))
{
	/**
	 * Returns the timezone the application has been set to display
	 * dates in. This might be different than the timezone set
	 * at the server level, as you often want to stores dates in UTC
	 * and convert them on the fly for the user.
	 * 返回应用程序设置用于显示日期的时区。
	 * 这可能与服务器级别的时区不同，因为您通常希望以UTC存储日期，并在用户使用时动态转换。
	 *
	 * @return string
	 */
	function app_timezone(): string
	{
		$config = config(App::class);

		return $config->appTimezone;
	}
}

if (! function_exists('cache'))
{
	/**
	 * A convenience method that provides access to the Cache
	 * object. If no parameter is provided, will return the object,
	 * otherwise, will attempt to return the cached value.
	 * 一个便捷方法，用于访问缓存对象。
	 * 如果未提供参数，则返回该对象；否则，尝试返回缓存的值。
	 *
	 * Examples:
	 *    cache()->save('foo', 'bar');
	 *    $foo = cache('bar');
	 *
	 * @param string|null $key
	 *
	 * @return CacheInterface|mixed
	 */
	function cache(string $key = null)
	{
		$cache = Services::cache();

		// No params - return cache object
		if (is_null($key))
		{
			return $cache;
		}

		// Still here? Retrieve the value.
		return $cache->get($key);
	}
}

if (! function_exists('clean_path'))
{
	/**
	 * A convenience method to clean paths for
	 * a nicer looking output. Useful for exception
	 * handling, error logging, etc.
	 * 一种方便的方法，用于清理路径以获得更美观的输出。
	 * 适用于异常处理、错误日志记录等场景。
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	function clean_path(string $path): string
	{
		// Resolve relative paths
		$path = realpath($path) ?: $path;

		switch (true)
		{
			case strpos($path, APPPATH) === 0:
				return 'APPPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(APPPATH));
			case strpos($path, SYSTEMPATH) === 0:
				return 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(SYSTEMPATH));
			case strpos($path, FCPATH) === 0:
				return 'FCPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(FCPATH));
			case defined('VENDORPATH') && strpos($path, VENDORPATH) === 0:
				return 'VENDORPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(VENDORPATH));
			case strpos($path, ROOTPATH) === 0:
				return 'ROOTPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(ROOTPATH));
			default:
				return $path;
		}
	}
}

if (! function_exists('command'))
{
	/**
	 * Runs a single command.
	 * Input expected in a single string as would
	 * be used on the command line itself:
	 * 执行单个命令。输入应为一个字符串，与在命令行中使用的方式相同：
	 *
	 *  > command('migrate:create SomeMigration');
	 *
	 * @param string $command
	 *
	 * @return false|string
	 */
	function command(string $command)
	{
		$runner      = service('commands');
		$regexString = '([^\s]+?)(?:\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
		$regexQuoted = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

		$args   = [];
		$length = strlen($command);
		$cursor = 0;

		/**
		 * Adopted from Symfony's `StringInput::tokenize()` with few changes.
		 *
		 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Input/StringInput.php
		 */
		while ($cursor < $length)
		{
			if (preg_match('/\s+/A', $command, $match, 0, $cursor))
			{
				// nothing to do
			}
			elseif (preg_match('/' . $regexQuoted . '/A', $command, $match, 0, $cursor))
			{
				$args[] = stripcslashes(substr($match[0], 1, strlen($match[0]) - 2));
			}
			elseif (preg_match('/' . $regexString . '/A', $command, $match, 0, $cursor))
			{
				$args[] = stripcslashes($match[1]);
			}
			else
			{
				// @codeCoverageIgnoreStart
				throw new InvalidArgumentException(sprintf('Unable to parse input near "... %s ...".', substr($command, $cursor, 10)));
				// @codeCoverageIgnoreEnd
			}

			$cursor += strlen($match[0]);
		}

		$command     = array_shift($args);
		$params      = [];
		$optionValue = false;

		foreach ($args as $i => $arg)
		{
			if (mb_strpos($arg, '-') !== 0)
			{
				if ($optionValue)
				{
					// if this was an option value, it was already
					// included in the previous iteration
					// 如果这是期权价值，它已经在之前的迭代中包含在内了。
					$optionValue = false;
				}
				else
				{
					// add to segments if not starting with '-'
					// and not an option value
					$params[] = $arg;
				}

				continue;
			}

			$arg   = ltrim($arg, '-');
			$value = null;

			if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0)
			{
				$value       = $args[$i + 1];
				$optionValue = true;
			}

			$params[$arg] = $value;
		}

		ob_start();
		$runner->run($command, $params);

		return ob_get_clean();
	}
}

if (! function_exists('config'))
{
	/**
	 * More simple way of getting config instances from Factories
	 * 从工厂获取配置实例的更简单的方法
	 *
	 * @param string  $name
	 * @param boolean $getShared
	 *
	 * @return mixed
	 */
	function config(string $name, bool $getShared = true)
	{
		return Factories::config($name, ['getShared' => $getShared]);
	}
}

if (! function_exists('csrf_token'))
{
	/**
	 * Returns the CSRF token name.
	 * Can be used in Views when building hidden inputs manually,
	 * or used in javascript vars when using APIs.
	 * 返回CSRF令牌的名称。
	 * 可在视图中手动构建隐藏输入时使用，或在调用API时用于JavaScript变量。
	 *
	 * @return string
	 */
	function csrf_token(): string
	{
		return Services::security()->getTokenName();
	}
}

if (! function_exists('csrf_header'))
{
	/**
	 * Returns the CSRF header name.
	 * Can be used in Views by adding it to the meta tag
	 * or used in javascript to define a header name when using APIs.
	 * 返回CSRF标头名称。
	 * 可在视图中通过添加到元标签中使用，或在JavaScript中用于API调用时定义标头名称。
	 *
	 * @return string
	 */
	function csrf_header(): string
	{
		return Services::security()->getHeaderName();
	}
}

if (! function_exists('csrf_hash'))
{
	/**
	 * Returns the current hash value for the CSRF protection.
	 * Can be used in Views when building hidden inputs manually,
	 * or used in javascript vars for API usage.
	 * 返回当前的CSRF保护哈希值。
	 * 可在视图中手动构建隐藏输入时使用，也可用于JavaScript变量以实现API调用。
	 *
	 * @return string
	 */
	function csrf_hash(): string
	{
		return Services::security()->getHash();
	}
}

if (! function_exists('csrf_field'))
{
	/**
	 * Generates a hidden input field for use within manually generated forms.
	 * 生成隐藏的输入字段，以便在手动生成的表单中使用。
	 *
	 * @param string|null $id
	 *
	 * @return string
	 */
	function csrf_field(string $id = null): string
	{
		return '<input type="hidden"' . (! empty($id) ? ' id="' . esc($id, 'attr') . '"' : '') . ' name="' . csrf_token() . '" value="' . csrf_hash() . '" />';
	}
}

if (! function_exists('csrf_meta'))
{
	/**
	 * Generates a meta tag for use within javascript calls.
	 * 生成用于javascript调用的元标记
	 *
	 * @param string|null $id
	 *
	 * @return string
	 */
	function csrf_meta(string $id = null): string
	{
		return '<meta' . (! empty($id) ? ' id="' . esc($id, 'attr') . '"' : '') . ' name="' . csrf_header() . '" content="' . csrf_hash() . '" />';
	}
}

if (! function_exists('db_connect'))
{
	/**
	 * Grabs a database connection and returns it to the user.
	 * 获取数据库连接并将其返回给用户。
	 *
	 * This is a convenience wrapper for \Config\Database::connect()
	 * and supports the same parameters. Namely:
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * If $getShared === false then a new connection instance will be provided,
	 * otherwise it will all calls will return the same instance.
	 *
	 * @param ConnectionInterface|array|string|null $db
	 * @param boolean                               $getShared
	 *
	 * @return BaseConnection
	 */
	function db_connect($db = null, bool $getShared = true)
	{
		return Database::connect($db, $getShared);
	}
}

if (! function_exists('dd'))
{
	/**
	 * Prints a Kint debug report and exits.
	 * 打印Kint调试报告并退出
	 *
	 * @param array ...$vars
	 *
	 * @codeCoverageIgnore Can't be tested ... exits
	 */
	function dd(...$vars)
	{
		// @codeCoverageIgnoreStart
		Kint::$aliases[] = 'dd';
		Kint::dump(...$vars);
		exit;
		// @codeCoverageIgnoreEnd
	}
}

if (! function_exists('env'))
{
	/**
	 * Allows user to retrieve values from the environment
	 * variables that have been set. Especially useful for
	 * retrieving values set from the .env file for
	 * use in config files.
	 * 允许用户从已设置的环境变量中获取值，特别适用于从 .env 文件中获取并用于配置文件的值。
	 *
	 * @param string      $key
	 * @param string|null $default
	 *
	 * @return mixed
	 */
	function env(string $key, $default = null)
	{
		$value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

		// Not found? Return the default value
		if ($value === false)
		{
			return $default;
		}

		// Handle any boolean values
		switch (strtolower($value))
		{
			case 'true':
				return true;
			case 'false':
				return false;
			case 'empty':
				return '';
			case 'null':
				return null;
		}

		return $value;
	}
}

if (! function_exists('esc'))
{
	/**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 * 出于安全考虑，对数据进行简单的自动转义处理。未来可能会考虑使其更复杂一些。
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * Valid context values: html, js, css, url, attr, raw, null
	 *
	 * @param string|array $data
	 * @param string       $context
	 * @param string       $encoding
	 *
	 * @return string|array
	 * @throws InvalidArgumentException
	 */
	function esc($data, string $context = 'html', string $encoding = null)
	{
		if (is_array($data))
		{
			foreach ($data as &$value)
			{
				$value = esc($value, $context);
			}
		}

		if (is_string($data))
		{
			$context = strtolower($context);

			// Provide a way to NOT escape data since
			// this could be called automatically by
			// the View library.
			if (empty($context) || $context === 'raw')
			{
				return $data;
			}

			if (! in_array($context, ['html', 'js', 'css', 'url', 'attr'], true))
			{
				throw new InvalidArgumentException('Invalid escape context provided.');
			}

			if ($context === 'attr')
			{
				$method = 'escapeHtmlAttr';
			}
			else
			{
				$method = 'escape' . ucfirst($context);
			}

			static $escaper;
			if (! $escaper)
			{
				$escaper = new Escaper($encoding);
			}

			if ($encoding && $escaper->getEncoding() !== $encoding)
			{
				$escaper = new Escaper($encoding);
			}

			$data = $escaper->$method($data);
		}

		return $data;
	}
}

if (! function_exists('force_https'))
{
	/**
	 * Used to force a page to be accessed in via HTTPS.
	 * Uses a standard redirect, plus will set the HSTS header
	 * for modern browsers that support, which gives best
	 * protection against man-in-the-middle attacks.
	 * 用于强制页面通过 HTTPS 访问。
	 * 使用标准重定向，并为支持的现代浏览器设置 HSTS 头部，从而提供最佳保护，防止中间人攻击。
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
	 *
	 * @param integer           $duration How long should the SSL header be set for? (in seconds)
	 *                                    Defaults to 1 year.
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 *
	 * @throws HTTPException
	 */
	function force_https(int $duration = 31536000, RequestInterface $request = null, ResponseInterface $response = null)
	{
		if (is_null($request))
		{
			$request = Services::request(null, true);
		}
		if (is_null($response))
		{
			$response = Services::response(null, true);
		}

		if ((ENVIRONMENT !== 'testing' && (is_cli() || $request->isSecure())) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'test'))
		{
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// If the session status is active, we should regenerate
		// the session ID for safety sake.
		if (ENVIRONMENT !== 'testing' && session_status() === PHP_SESSION_ACTIVE)
		{
			// @codeCoverageIgnoreStart
			Services::session(null, true)
				->regenerate();
			// @codeCoverageIgnoreEnd
		}

		$baseURL = config(App::class)->baseURL;

		if (strpos($baseURL, 'https://') === 0)
		{
			$baseURL = (string) substr($baseURL, strlen('https://'));
		}
		elseif (strpos($baseURL, 'http://') === 0)
		{
			$baseURL = (string) substr($baseURL, strlen('http://'));
		}

		$uri = URI::createURIString(
			'https', $baseURL, $request->uri->getPath(), // Absolute URIs should use a "/" for an empty path
			$request->uri->getQuery(), $request->uri->getFragment()
		);

		// Set an HSTS header
		$response->setHeader('Strict-Transport-Security', 'max-age=' . $duration);
		$response->redirect($uri);
		$response->sendHeaders();

		if (ENVIRONMENT !== 'testing')
		{
			// @codeCoverageIgnoreStart
			exit();
			// @codeCoverageIgnoreEnd
		}
	}
}

if (! function_exists('function_usable'))
{
	/**
	 * Function usable
	 * 使用功能。
	 *
	 * Executes a function_exists() check, and if the Suhosin PHP
	 * extension is loaded - checks whether the function that is
	 * checked might be disabled in there as well.
	 *
	 * This is useful as function_exists() will return FALSE for
	 * functions disabled via the *disable_functions* php.ini
	 * setting, but not for *suhosin.executor.func.blacklist* and
	 * *suhosin.executor.disable_eval*. These settings will just
	 * terminate script execution if a disabled function is executed.
	 *
	 * The above described behavior turned out to be a bug in Suhosin,
	 * but even though a fix was committed for 0.9.34 on 2012-02-12,
	 * that version is yet to be released. This function will therefore
	 * be just temporary, but would probably be kept for a few years.
	 *
	 * @link   http://www.hardened-php.net/suhosin/
	 * @param  string $functionName Function to check for
	 * @return boolean    TRUE if the function exists and is safe to call,
	 *             FALSE otherwise.
	 *
	 * @codeCoverageIgnore This is too exotic
	 */
	function function_usable(string $functionName): bool
	{
		static $_suhosin_func_blacklist;

		if (function_exists($functionName))
		{
			if (! isset($_suhosin_func_blacklist))
			{
				$_suhosin_func_blacklist = extension_loaded('suhosin') ? explode(',', trim(ini_get('suhosin.executor.func.blacklist'))) : [];
			}

			return ! in_array($functionName, $_suhosin_func_blacklist, true);
		}

		return false;
	}
}

if (! function_exists('helper'))
{
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 * 将辅助文件加载到内存中。支持命名空间辅助函数，包括位于命名空间目录下的“helpers”目录中的和外部的。
	 *
	 * Will load ALL helpers of the matching name, in the following order:
	 *   1. app/Helpers
	 *   2. {namespace}/Helpers
	 *   3. system/Helpers
	 *
	 * @param  string|array $filenames
	 * @throws FileNotFoundException
	 */
	function helper($filenames)
	{
		static $loaded = [];

		$loader = Services::locator(true);

		if (! is_array($filenames))
		{
			$filenames = [$filenames];
		}

		// Store a list of all files to include...
		$includes = [];

		foreach ($filenames as $filename)
		{
			// Store our system and application helper
			// versions so that we can control the load ordering.
			$systemHelper  = null;
			$appHelper     = null;
			$localIncludes = [];

			if (strpos($filename, '_helper') === false)
			{
				$filename .= '_helper';
			}

			// Check if this helper has already been loaded
			if (in_array($filename, $loaded, true))
			{
				continue;
			}

			// If the file is namespaced, we'll just grab that
			// file and not search for any others
			if (strpos($filename, '\\') !== false)
			{
				$path = $loader->locateFile($filename, 'Helpers');

				if (empty($path))
				{
					throw FileNotFoundException::forFileNotFound($filename);
				}

				$includes[] = $path;
				$loaded[]   = $filename;
			}

			// No namespaces, so search in all available locations
			else
			{
				$paths = $loader->search('Helpers/' . $filename);

				if (! empty($paths))
				{
					foreach ($paths as $path)
					{
						if (strpos($path, APPPATH) === 0)
						{
							// @codeCoverageIgnoreStart
							$appHelper = $path;
							// @codeCoverageIgnoreEnd
						}
						elseif (strpos($path, SYSTEMPATH) === 0)
						{
							$systemHelper = $path;
						}
						else
						{
							$localIncludes[] = $path;
							$loaded[]        = $filename;
						}
					}
				}

				// App-level helpers should override all others
				if (! empty($appHelper))
				{
					// @codeCoverageIgnoreStart
					$includes[] = $appHelper;
					$loaded[]   = $filename;
					// @codeCoverageIgnoreEnd
				}

				// All namespaced files get added in next
				$includes = array_merge($includes, $localIncludes);

				// And the system default one should be added in last.
				if (! empty($systemHelper))
				{
					$includes[] = $systemHelper;
					$loaded[]   = $filename;
				}
			}
		}

		// Now actually include all of the files
		if (! empty($includes))
		{
			foreach ($includes as $path)
			{
				include_once($path);
			}
		}
	}
}

if (! function_exists('is_cli'))
{
	/**
	 * Is CLI?
	 * CLI吗?
	 *
	 * Test to see if a request was made from the command line.
	 * 测试是否从命令行发出请求。
	 *
	 * @return boolean
	 */
	function is_cli(): bool
	{
		return (PHP_SAPI === 'cli' || defined('STDIN'));
	}
}

if (! function_exists('is_really_writable'))
{
	/**
	 * Tests for file writability
	 * 测试文件可写性。
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link https://bugs.php.net/bug.php?id=54709
	 *
	 * @param string $file
	 *
	 * @return boolean
	 *
	 * @throws             Exception
	 * @codeCoverageIgnore Not practical to test, as travis runs on linux
	 */
	function is_really_writable(string $file): bool
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR === '/' || ! ini_get('safe_mode'))
		{
			return is_writable($file);
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if (is_dir($file))
		{
			$file = rtrim($file, '/') . '/' . bin2hex(random_bytes(16));
			if (($fp = @fopen($file, 'ab')) === false)
			{
				return false;
			}

			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);

			return true;
		}

		if (! is_file($file) || ($fp = @fopen($file, 'ab')) === false)
		{
			return false;
		}

		fclose($fp);

		return true;
	}
}

if (! function_exists('lang'))
{
	/**
	 * A convenience method to translate a string or array of them and format
	 * the result with the intl extension's MessageFormatter.
	 * 一个便捷的方法，用于将字符串或字符串数组进行翻译，并使用 intl 扩展的 MessageFormatter 格式化结果。
	 *
	 * @param string      $line
	 * @param array       $args
	 * @param string|null $locale
	 *
	 * @return string
	 */
	function lang(string $line, array $args = [], string $locale = null)
	{
		return Services::language($locale)
			->getLine($line, $args);
	}
}

if (! function_exists('log_message'))
{
	/**
	 * A convenience/compatibility method for logging events through
	 * the Log system.
	 *
	 * Allowed log levels are:
	 *  - emergency
	 *  - alert
	 *  - critical
	 *  - error
	 *  - warning
	 *  - notice
	 *  - info
	 *  - debug
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return mixed
	 */
	function log_message(string $level, string $message, array $context = [])
	{
		// When running tests, we want to always ensure that the
		// TestLogger is running, which provides utilities for
		// for asserting that logs were called in the test code.
		if (ENVIRONMENT === 'testing')
		{
			$logger = new TestLogger(new Logger());

			return $logger->log($level, $message, $context);
		}

		// @codeCoverageIgnoreStart
		return Services::logger(true)
			->log($level, $message, $context);
		// @codeCoverageIgnoreEnd
	}
}

if (! function_exists('model'))
{
	/**
	 * More simple way of getting model instances from Factories
	 * 从工厂获取模型实例的更简单的方法
	 *
	 * @param string                   $name
	 * @param boolean                  $getShared
	 * @param ConnectionInterface|null $conn
	 *
	 * @return mixed
	 */
	function model(string $name, bool $getShared = true, ConnectionInterface &$conn = null)
	{
		return Factories::models($name, ['getShared' => $getShared], $conn);
	}
}

if (! function_exists('old'))
{
	/**
	 * Provides access to "old input" that was set in the session
	 * during a redirect()->withInput().
	 *
	 * @param string         $key
	 * @param null           $default
	 * @param string|boolean $escape
	 *
	 * @return mixed|null
	 */
	function old(string $key, $default = null, $escape = 'html')
	{
		// Ensure the session is loaded
		if (session_status() === PHP_SESSION_NONE && ENVIRONMENT !== 'testing')
		{
			// @codeCoverageIgnoreStart
			session();
			// @codeCoverageIgnoreEnd
		}

		$request = Services::request();

		$value = $request->getOldInput($key);

		// Return the default value if nothing
		// found in the old input.
		if (is_null($value))
		{
			return $default;
		}

		// If the result was serialized array or string, then unserialize it for use...
		if (is_string($value))
		{
			if (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0)
			{
				$value = unserialize($value);
			}
		}

		return $escape === false ? $value : esc($value, $escape);
	}
}

if (! function_exists('redirect'))
{
	/**
	 * Convenience method that works with the current global $request and
	 * $router instances to redirect using named/reverse-routed routes
	 * to determine the URL to go to. If nothing is found, will treat
	 * as a traditional redirect and pass the string in, letting
	 * $response->redirect() determine the correct method and code.
	 * 一种便捷的方法，通过当前全局的 $request 和 $router 实例，使用命名/反向路由来重定向，以确定应访问的 URL。
	 *
	 * If more control is needed, you must use $response->redirect explicitly.
	 *
	 * @param string $route
	 *
	 * @return RedirectResponse
	 */
	function redirect(string $route = null): RedirectResponse
	{
		$response = Services::redirectresponse(null, true);

		if (! empty($route))
		{
			return $response->route($route);
		}

		return $response;
	}
}

if (! function_exists('remove_invisible_characters'))
{
	/**
	 * Remove Invisible Characters
	 * 删除不可见字符。
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param string  $str
	 * @param boolean $urlEncoded
	 *
	 * @return string
	 */
	function remove_invisible_characters(string $str, bool $urlEncoded = true): string
	{
		$nonDisplayables = [];

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($urlEncoded)
		{
			$nonDisplayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
			$nonDisplayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
		}

		$nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($nonDisplayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}
}

if (! function_exists('route_to'))
{
	/**
	 * Given a controller/method string and any params,
	 * will attempt to build the relative URL to the
	 * matching route.
	 * 给定一个控制器/方法字符串和任意参数，将尝试构建指向匹配路由的相对URL。
	 *
	 * NOTE: This requires the controller/method to
	 * have a route defined in the routes Config file.
	 *
	 * @param string $method
	 * @param mixed  ...$params
	 *
	 * @return false|string
	 */
	function route_to(string $method, ...$params)
	{
		return Services::routes()->reverseRoute($method, ...$params);
	}
}

if (! function_exists('session'))
{
	/**
	 * A convenience method for accessing the session instance,
	 * or an item that has been set in the session.
	 * 访问会话实例或会话中已设置的项目的便捷方法。
	 *
	 * Examples:
	 *    session()->set('foo', 'bar');
	 *    $foo = session('bar');
	 *
	 * @param string $val
	 *
	 * @return Session|mixed|null
	 */
	function session(string $val = null)
	{
		$session = Services::session();

		// Returning a single item?
		if (is_string($val))
		{
			return $session->get($val);
		}

		return $session;
	}
}

if (! function_exists('service'))
{
	/**
	 * Allows cleaner access to the Services Config file.
	 * Always returns a SHARED instance of the class, so
	 * calling the function multiple times should always
	 * return the same instance.
	 * 允许更便捷地访问服务配置文件。
	 * 始终返回类的SHARED实例，因此多次调用该函数应始终返回相同的实例。
	 *
	 * These are equal:
	 *  - $timer = service('timer')
	 *  - $timer = \CodeIgniter\Config\Services::timer();
	 *
	 * @param string $name
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	function service(string $name, ...$params)
	{
		return Services::$name(...$params);
	}
}

if (! function_exists('single_service'))
{
	/**
	 * Always returns a new instance of the class.
	 * 总是返回类的新实例
	 *
	 * @param string     $name
	 * @param array|null $params
	 *
	 * @return mixed
	 */
	function single_service(string $name, ...$params)
	{
		$service = Services::serviceExists($name);

		if ($service === null)
		{
			// The service is not defined anywhere so just return.
			return null;
		}

		$method = new ReflectionMethod($service, $name);
		$count  = $method->getNumberOfParameters();
		$mParam = $method->getParameters();
		$params = $params ?? [];

		if ($count === 1)
		{
			// This service needs only one argument, which is the shared
			// instance flag, so let's wrap up and pass false here.
			return $service::$name(false);
		}

		// Fill in the params with the defaults, but stop before the last
		for ($startIndex = count($params); $startIndex <= $count - 2; $startIndex++)
		{
			$params[$startIndex] = $mParam[$startIndex]->getDefaultValue();
		}

		// Ensure the last argument will not create a shared instance
		$params[$count - 1] = false;

		return $service::$name(...$params);
	}
}

if (! function_exists('slash_item'))
{
	//Unlike CI3, this function is placed here because
	//it's not a config, or part of a config.
	/**
	 * Fetch a config file item with slash appended (if not empty)
	 *
	 * @param string $item Config item name
	 *
	 * @return string|null The configuration item or NULL if
	 * the item doesn't exist
	 */
	function slash_item(string $item): ?string
	{
		$config     = config(App::class);
		$configItem = $config->{$item};

		if (! isset($configItem) || empty(trim($configItem)))
		{
			return $configItem;
		}

		return rtrim($configItem, '/') . '/';
	}
}

if (! function_exists('stringify_attributes'))
{
	/**
	 * Stringify attributes for use in HTML tags.
	 * 对HTML标签中使用的属性进行字符串化。
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 * 用于将字符串、数组或属性对象转换为字符串的辅助函数。
	 *
	 * @param mixed   $attributes string, array, object
	 * @param boolean $js
	 *
	 * @return string
	 */
	function stringify_attributes($attributes, bool $js = false): string
	{
		$atts = '';

		if (empty($attributes))
		{
			return $atts;
		}

		if (is_string($attributes))
		{
			return ' ' . $attributes;
		}

		$attributes = (array) $attributes;

		foreach ($attributes as $key => $val)
		{
			$atts .= ($js) ? $key . '=' . esc($val, 'js') . ',' : ' ' . $key . '="' . esc($val, 'attr') . '"';
		}

		return rtrim($atts, ',');
	}
}

if (! function_exists('timer'))
{
	/**
	 * A convenience method for working with the timer.
	 * If no parameter is passed, it will return the timer instance,
	 * otherwise will start or stop the timer intelligently.
	 * 一个用于操作定时器的便捷方法。
	 * 如果未传入参数，将返回定时器实例；否则会智能地启动或停止定时器。
	 *
	 * @param string|null $name
	 *
	 * @return Timer|mixed
	 */
	function timer(string $name = null)
	{
		$timer = Services::timer();

		if (empty($name))
		{
			return $timer;
		}

		if ($timer->has($name))
		{
			return $timer->stop($name);
		}

		return $timer->start($name);
	}
}

if (! function_exists('trace'))
{
	/**
	 * Provides a backtrace to the current execution point, from Kint.
	 * 提供从Kint到当前执行点的回溯
	 */
	function trace()
	{
		Kint::$aliases[] = 'trace';
		Kint::trace();
	}
}

if (! function_exists('view'))
{
	/**
	 * Grabs the current RendererInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 * 获取当前与 RendererInterface 兼容的类，并告诉它渲染指定的视图。
	 * 提供了一个便捷的方法，可在控制器、库和路由闭包中使用。
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function view(string $name, array $data = [], array $options = []): string
	{
		/**
		 * @var CodeIgniter\View\View $renderer
		 */
		$renderer = Services::renderer();

		$saveData = config(View::class)->saveData;

		if (array_key_exists('saveData', $options))
		{
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $renderer->setData($data, 'raw')
						->render($name, $options, $saveData);
	}
}

if (! function_exists('view_cell'))
{
	/**
	 * View cells are used within views to insert HTML chunks that are managed
	 * by other classes.
	 * 视图单元格用于在视图中插入由其他类管理的HTML片段
	 *
	 * @param string      $library
	 * @param null        $params
	 * @param integer     $ttl
	 * @param string|null $cacheName
	 *
	 * @return string
	 * @throws ReflectionException
	 */
	function view_cell(string $library, $params = null, int $ttl = 0, string $cacheName = null): string
	{
		return Services::viewcell()
			->render($library, $params, $ttl, $cacheName);
	}
}
