<?php
/**
 * CodeIgniter，HTTP，CLI 请求
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use Config\App;
use RuntimeException;

/**
 * Class CLIRequest
 * CLI 请求类。
 *
 * Represents a request from the command-line. Provides additional
 * tools to interact with that request since CLI requests are not
 * static like HTTP requests might be.
 * 代表来自命令行的请求。由于CLI请求不像HTTP请求那样静态，因此提供了与之交互的额外工具。
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under.
 *
 * http://fuelphp.com
 */
class CLIRequest extends Request
{
	/**
	 * Stores the segments of our cli "URI" command.
	 * 存储cli“URI”命令的段
	 *
	 * @var array
	 */
	protected $segments = [];

	/**
	 * Command line options and their values.
	 * 命令行选项及其值
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Set the expected HTTP verb
	 * 设置预期的HTTP verb
	 *
	 * @var string
	 */
	protected $method = 'cli';

	/**
	 * Constructor
	 * 构造函数
	 *
	 * @param App $config
	 */
	public function __construct(App $config)
	{
		if (! is_cli())
		{
			throw new RuntimeException(static::class . ' needs to run from the command line.'); // @codeCoverageIgnore
		}

		parent::__construct($config);

		// Don't terminate the script when the cli's tty goes away
		// 不要在cli的tty消失时终止脚本
		ignore_user_abort(true);

		$this->parseCommand();
	}

	/**
	 * Returns the "path" of the request script so that it can be used
	 * in routing to the appropriate controller/method.
	 * 返回请求脚本的“路径”，以便在路由到相应的控制器/方法时使用。
	 *
	 * The path is determined by treating the command line arguments
	 * as if it were a URL - up until we hit our first option.
	 *
	 * Example:
	 *      php index.php users 21 profile -foo bar
	 *
	 *      // Routes to /users/21/profile (index is removed for routing sake)
	 *      // with the option foo = bar.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		$path = implode('/', $this->segments);

		return empty($path) ? '' : $path;
	}

	/**
	 * Returns an associative array of all CLI options found, with
	 * their values.
	 * 返回一个关联数组，包含所有找到的命令行选项及其对应的值。
	 *
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Returns the path segments.
	 * 返回路径段
	 *
	 * @return array
	 */
	public function getSegments(): array
	{
		return $this->segments;
	}

	/**
	 * Returns the value for a single CLI option that was passed in.
	 * 返回传入的单个CLI选项的值
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function getOption(string $key)
	{
		return $this->options[$key] ?? null;
	}

	/**
	 * Returns the options as a string, suitable for passing along on
	 * the CLI to other commands.
	 * 返回选项作为字符串，适合在命令行中传递给其他命令。
	 *
	 * Example:
	 *      $options = [
	 *          'foo' => 'bar',
	 *          'baz' => 'queue some stuff'
	 *      ];
	 *
	 *      getOptionString() = '-foo bar -baz "queue some stuff"'
	 *
	 * @param boolean $useLongOpts
	 *
	 * @return string
	 */
	public function getOptionString(bool $useLongOpts = false): string
	{
		if (empty($this->options))
		{
			return '';
		}

		$out = '';

		foreach ($this->options as $name => $value)
		{
			if ($useLongOpts && mb_strlen($name) > 1)
			{
				$out .= "--{$name} ";
			}
			else
			{
				$out .= "-{$name} ";
			}

			// If there's a space, we need to group
			// so it will pass correctly.
			if (mb_strpos($value, ' ') !== false)
			{
				$out .= '"' . $value . '" ';
			}
			elseif ($value !== null)
			{
				$out .= "{$value} ";
			}
		}

		return trim($out);
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the command line it was called from and collects all options
	 * and valid segments.
	 * 解析调用它的命令行，并收集所有选项和有效片段。
	 *
	 * NOTE: I tried to use getopt but had it fail occasionally to find
	 * any options, where argv has always had our back.
	 */
	protected function parseCommand()
	{
		$args = $this->getServer('argv');
		array_shift($args); // Scrap index.php

		$optionValue = false;

		foreach ($args as $i => $arg)
		{
			if (mb_strpos($arg, '-') !== 0)
			{
				if ($optionValue)
				{
					$optionValue = false;
				}
				else
				{
					$this->segments[] = filter_var($arg, FILTER_SANITIZE_STRING);
				}

				continue;
			}

			$arg   = filter_var(ltrim($arg, '-'), FILTER_SANITIZE_STRING);
			$value = null;

			if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0)
			{
				$value       = filter_var($args[$i + 1], FILTER_SANITIZE_STRING);
				$optionValue = true;
			}

			$this->options[$arg] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if this request was made from the command line (CLI).
	 * 确定此请求是否从命令行（CLI）发出
	 *
	 * @return boolean
	 */
	public function isCLI(): bool
	{
		return is_cli();
	}

	//--------------------------------------------------------------------
}
