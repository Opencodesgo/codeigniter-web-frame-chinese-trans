<?php
/**
 * CodeIgniter，CLI，基本命令
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use Psr\Log\LoggerInterface;
use ReflectionException;
use Throwable;

/**
 * Class BaseCommand
 * 基本命令类。
 */
abstract class BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 * 命令列表中该组所包含的命令
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * The Command's name
	 * 命令的名称
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * the Command's usage description
	 * 命令的用法说明
	 *
	 * @var string
	 */
	protected $usage;

	/**
	 * the Command's short description
	 * 命令的简短描述
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * the Command's options description
	 * 命令的选项描述
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * the Command's Arguments description
	 * 命令的参数描述
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The Logger to use for a command
	 * 用于命令的记录器
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Instance of Commands so
	 * commands can call other commands.
	 * 命令实例，以便命令可以调用其他命令。
	 *
	 * @var Commands
	 */
	protected $commands;

	//--------------------------------------------------------------------

	/**
	 * BaseCommand constructor.
	 * BaseCommand构造函数
	 *
	 * @param LoggerInterface $logger
	 * @param Commands        $commands
	 */
	public function __construct(LoggerInterface $logger, Commands $commands)
	{
		$this->logger   = $logger;
		$this->commands = $commands;
	}

	//--------------------------------------------------------------------

	/**
	 * Actually execute a command.
	 * This has to be over-ridden in any concrete implementation.
	 * 实际执行一个命令。这在任何具体实现中都必须被覆盖。
	 *
	 * @param array $params
	 */
	abstract public function run(array $params);

	//--------------------------------------------------------------------

	/**
	 * Can be used by a command to run other commands.
	 * 可用于命令运行其他命令
	 *
	 * @param string $command
	 * @param array  $params
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	protected function call(string $command, array $params = [])
	{
		return $this->commands->run($command, $params);
	}

	//--------------------------------------------------------------------

	/**
	 * A simple method to display an error with line/file, in child commands.
	 * 在子命令中显示行/文件错误的简单方法
	 *
	 * @param Throwable $e
	 */
	protected function showError(Throwable $e)
	{
		$exception = $e;
		$message   = $e->getMessage();

		require APPPATH . 'Views/errors/cli/error_exception.php';
	}

	//--------------------------------------------------------------------

	/**
	 * Show Help includes (Usage, Arguments, Description, Options).
	 * 显示帮助包括（用法，参数，描述，选项）
	 */
	public function showHelp()
	{
		CLI::write(lang('CLI.helpUsage'), 'yellow');

		if (! empty($this->usage))
		{
			$usage = $this->usage;
		}
		else
		{
			$usage = $this->name;

			if (! empty($this->arguments))
			{
				$usage .= ' [arguments]';
			}
		}

		CLI::write($this->setPad($usage, 0, 0, 2));

		if (! empty($this->description))
		{
			CLI::newLine();
			CLI::write(lang('CLI.helpDescription'), 'yellow');
			CLI::write($this->setPad($this->description, 0, 0, 2));
		}

		if (! empty($this->arguments))
		{
			CLI::newLine();
			CLI::write(lang('CLI.helpArguments'), 'yellow');
			$length = max(array_map('strlen', array_keys($this->arguments)));

			foreach ($this->arguments as $argument => $description)
			{
				CLI::write(CLI::color($this->setPad($argument, $length, 2, 2), 'green') . $description);
			}
		}

		if (! empty($this->options))
		{
			CLI::newLine();
			CLI::write(lang('CLI.helpOptions'), 'yellow');
			$length = max(array_map('strlen', array_keys($this->options)));

			foreach ($this->options as $option => $description)
			{
				CLI::write(CLI::color($this->setPad($option, $length, 2, 2), 'green') . $description);
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Pads our string out so that all titles are the same length to nicely line up descriptions.
	 * 填充字符串，使所有标题都具有相同的长度，以便很好地排列描述。
	 *
	 * @param string  $item
	 * @param integer $max
	 * @param integer $extra  How many extra spaces to add at the end
	 * @param integer $indent
	 *
	 * @return string
	 */
	public function setPad(string $item, int $max, int $extra = 2, int $indent = 0): string
	{
		$max += $extra + $indent;

		return str_pad(str_repeat(' ', $indent) . $item, $max);
	}

	//--------------------------------------------------------------------

	/**
	 * Get pad for $key => $value array output
	 * 获取$key => $value数组输出的pad
	 *
	 * @param array   $array
	 * @param integer $pad
	 *
	 * @return integer
	 *
	 * @deprecated Use setPad() instead.
	 *
	 * @codeCoverageIgnore
	 */
	public function getPad(array $array, int $pad): int
	{
		$max = 0;
		foreach ($array as $key => $value)
		{
			$max = max($max, strlen($key));
		}
		return $max + $pad;
	}

	//--------------------------------------------------------------------

	/**
	 * Makes it simple to access our protected properties.
	 * 使访问受保护的属性变得简单
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Makes it simple to check our protected properties.
	 * 使检查受保护的属性变得简单
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function __isset(string $key): bool
	{
		return isset($this->$key);
	}
}
