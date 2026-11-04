<?php
/**
 * CodeIgniter，配置，Dot Env
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use InvalidArgumentException;

/**
 * Environment-specific configuration
 * 特定环境的配置。
 */
class DotEnv
{
	/**
	 * The directory where the .env file can be located.
	 * 可以找到.env文件的目录
	 *
	 * @var string
	 */
	protected $path;

	//--------------------------------------------------------------------

	/**
	 * Builds the path to our file.
	 * 构建文件的路径
	 *
	 * @param string $path
	 * @param string $file
	 */
	public function __construct(string $path, string $file = '.env')
	{
		$this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
	}

	//--------------------------------------------------------------------

	/**
	 * The main entry point, will load the .env file and process it
	 * so that we end up with all settings in the PHP environment vars
	 * (i.e. getenv(), $_ENV, and $_SERVER)
	 * 主入口点将加载 .env 文件并进行处理，使最终所有设置都存储在 PHP 环境变量中。
	 *
	 * @return boolean
	 */
	public function load(): bool
	{
		$vars = $this->parse();

		return ($vars === null ? false : true);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse the .env file into an array of key => value
	 * 解析.env文件转换为key =>值的数组
	 *
	 * @return array|null
	 */
	public function parse(): ?array
	{
		// We don't want to enforce the presence of a .env file, they should be optional.
		// 我们不想强制a的存在.env文件，它们应该是可选的。
		if (! is_file($this->path))
		{
			return null;
		}

		// Ensure the file is readable
		if (! is_readable($this->path))
		{
			throw new InvalidArgumentException("The .env file is not readable: {$this->path}");
		}

		$vars = [];

		$lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($lines as $line)
		{
			// Is it a comment?
			if (strpos(trim($line), '#') === 0)
			{
				continue;
			}

			// If there is an equal sign, then we know we are assigning a variable.
			// 如果有一个等号，那么我们就知道我们在赋值一个变量。
			if (strpos($line, '=') !== false)
			{
				list($name, $value) = $this->normaliseVariable($line);
				$vars[$name]        = $value;
				$this->setVariable($name, $value);
			}
		}

		return $vars;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the variable into the environment. Will parse the string
	 * first to look for {name}={value} pattern, ensure that nested
	 * variables are handled, and strip it of single and double quotes.
	 * 将变量设置为环境。将解析字符串*首先查找{ name } = { value }模式,确保处理嵌套变量,并删除单引号和双引号。
	 *
	 * @param string $name
	 * @param string $value
	 */
	protected function setVariable(string $name, string $value = '')
	{
		if (! getenv($name, true))
		{
			putenv("$name=$value");
		}

		if (empty($_ENV[$name]))
		{
			$_ENV[$name] = $value;
		}

		if (empty($_SERVER[$name]))
		{
			$_SERVER[$name] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Parses for assignment, cleans the $name and $value, and ensures
	 * that nested variables are handled.
	 * 用于赋值、$ name和$ value的解析,并确保处理嵌套变量。
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return array
	 */
	public function normaliseVariable(string $name, string $value = ''): array
	{
		// Split our compound string into its parts.
		if (strpos($name, '=') !== false)
		{
			list($name, $value) = explode('=', $name, 2);
		}

		$name  = trim($name);
		$value = trim($value);

		// Sanitize the name
		$name = str_replace(['export', '\'', '"'], '', $name);

		// Sanitize the value
		$value = $this->sanitizeValue($value);

		$value = $this->resolveNestedVariables($value);

		return [
			$name,
			$value,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Strips quotes from the environment variable value.
	 * 引用来自环境变量值的引用。
	 *
	 * This was borrowed from the excellent phpdotenv with very few changes.
	 * https://github.com/vlucas/phpdotenv
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function sanitizeValue(string $value): string
	{
		if (! $value)
		{
			return $value;
		}

		// Does it begin with a quote?
		// 它是从引号开始的吗?
		if (strpbrk($value[0], '"\'') !== false)
		{
			// value starts with a quote
			$quote        = $value[0];
			$regexPattern = sprintf(
					'/^
					%1$s          # match a quote at the start of the value
					(             # capturing sub-pattern used
								  (?:          # we do not need to capture this
								   [^%1$s\\\\] # any character other than a quote or backslash
								   |\\\\\\\\   # or two backslashes together
								   |\\\\%1$s   # or an escaped quote e.g \"
								  )*           # as many characters that match the previous rules
					)             # end of the capturing sub-pattern
					%1$s          # and the closing quote
					.*$           # and discard any string after the closing quote
					/mx', $quote
			);

			$value = preg_replace($regexPattern, '$1', $value);
			$value = str_replace("\\$quote", $quote, $value);
			$value = str_replace('\\\\', '\\', $value);
		}
		else
		{
			$parts = explode(' #', $value, 2);

			$value = trim($parts[0]);

			// Unquoted values cannot contain whitespace
			if (preg_match('/\s+/', $value) > 0)
			{
				throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
			}
		}

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 *  Resolve the nested variables.
	 *  解决嵌套变量。
	 *
	 * Look for ${varname} patterns in the variable value and replace with an existing
	 * environment variable.
	 * 在变量值中查找$ { varname }模式,并替换现有的环境变量。
	 *
	 * This was borrowed from the excellent phpdotenv with very few changes.
	 * https://github.com/vlucas/phpdotenv
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function resolveNestedVariables(string $value): string
	{
		if (strpos($value, '$') !== false)
		{
			$value = preg_replace_callback(
				'/\${([a-zA-Z0-9_]+)}/',
				function ($matchedPatterns) {
					$nestedVariable = $this->getVariable($matchedPatterns[1]);

					if (is_null($nestedVariable))
					{
						return $matchedPatterns[0];
					}

					return $nestedVariable;
				},
				$value
			);
		}

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Search the different places for environment variables and return first value found.
	 * 搜索不同的环境变量,并返回第一个值。
	 *
	 * This was borrowed from the excellent phpdotenv with very few changes.
	 * https://github.com/vlucas/phpdotenv
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	protected function getVariable(string $name)
	{
		switch (true)
		{
			case array_key_exists($name, $_ENV):
				return $_ENV[$name];
			case array_key_exists($name, $_SERVER):
				return $_SERVER[$name];
			default:
				$value = getenv($name);

				// switch getenv default to null
				// 将getenv default设置为null
				return $value === false ? null : $value;
		}
	}

	//--------------------------------------------------------------------
}
