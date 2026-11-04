<?php
/**
 * CodeIgniter，CLI，发生器特征
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

use Config\Services;
use Throwable;

/**
 * GeneratorTrait contains a collection of methods
 * to build the commands that generates a file.
 * GeneratorTrait 包含一组用于构建生成文件命令的方法。
 */
trait GeneratorTrait
{
	/**
	 * Component Name
	 * 组件名称
	 *
	 * @var string
	 */
	protected $component;

	/**
	 * File directory
	 * 文件目录
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * View template name
	 * 视图模板名称
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Whether to require class name.
	 * 是否需要类名
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $hasClassName = true;

	/**
	 * Whether to sort class imports.
	 * 是否对类导入进行排序
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $sortImports = true;

	/**
	 * The params array for easy access by other methods.
	 * 参数数组便于其他方法访问
	 *
	 * @internal
	 *
	 * @var array
	 */
	private $params = [];

	/**
	 * Execute the command.
	 * 执行命令
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	protected function execute(array $params): void
	{
		$this->params = $params;

		if ($this->getOption('namespace') === 'CodeIgniter')
		{
			// @codeCoverageIgnoreStart
			CLI::write(lang('CLI.generator.usingCINamespace'), 'yellow');
			CLI::newLine();

			if (CLI::prompt('Are you sure you want to continue?', ['y', 'n'], 'required') === 'n')
			{
				CLI::newLine();
				CLI::write(lang('CLI.generator.cancelOperation'), 'yellow');
				CLI::newLine();

				return;
			}

			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		// Get the fully qualified class name from the input.
		// 从输入中获取完全限定类名
		$class = $this->qualifyClassName();

		// Get the file path from class name.
		$path = $this->buildPath($class);

		// Check if path is empty.
		// 检查路径是否空
		if (empty($path))
		{
			return;
		}

		$isFile = is_file($path);

		// Overwriting files unknowingly is a serious annoyance, So we'll check if
		// we are duplicating things, If 'force' option is not supplied, we bail.
		// 无意中覆盖文件会带来严重困扰，因此我们会检查是否正在重复操作。如果未提供“force”选项，我们将直接退出。
		if (! $this->getOption('force') && $isFile)
		{
			CLI::error(lang('CLI.generator.fileExist', [clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return;
		}

		// Check if the directory to save the file is existing.
		// 检查保存文件的目录是否存在
		$dir = dirname($path);

		if (! is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}

		helper('filesystem');

		// Build the class based on the details we have, We'll be getting our file
		// contents from the template, and then we'll do the necessary replacements.
		// 根据我们已有的信息构建类，我们将从模板中获取文件内容，然后进行必要的替换。
		if (! write_file($path, $this->buildContent($class)))
		{
			// @codeCoverageIgnoreStart
			CLI::error(lang('CLI.generator.fileError', [clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return;
			// @codeCoverageIgnoreEnd
		}

		if ($this->getOption('force') && $isFile)
		{
			CLI::write(lang('CLI.generator.fileOverwrite', [clean_path($path)]), 'yellow');
			CLI::newLine();

			return;
		}

		CLI::write(lang('CLI.generator.fileCreate', [clean_path($path)]), 'green');
		CLI::newLine();
	}

	/**
	 * Prepare options and do the necessary replacements.
	 * 准备备选方案并进行必要的替换
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepare(string $class): string
	{
		return $this->parseTemplate($class);
	}

	/**
	 * Change file basename before saving.
	 * 保存前更改文件名称。
	 *
	 * Useful for components where the file name has a date.
	 * 对于文件名包含日期的组件非常有用。
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function basename(string $filename): string
	{
		return basename($filename);
	}

	/**
	 * Parses the class name and checks if it is already qualified.
	 * 解析类名并检查它是否已被限定
	 *
	 * @return string
	 */
	protected function qualifyClassName(): string
	{
		// Gets the class name from input.
		// 从输入获取类名
		$class = $this->params[0] ?? CLI::getSegment(2);

		if (is_null($class) && $this->hasClassName)
		{
			// @codeCoverageIgnoreStart
			$class = CLI::prompt(lang('CLI.generator.className'), null, 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		helper('inflector');

		$component = strtolower(singular($this->component));
		$class     = strtolower($class);
		$class     = strpos($class, $component) !== false ? str_replace($component, ucfirst($component), $class) : $class;

		if ($this->getOption('suffix') && ! strripos($class, $component))
		{
			$class .= ucfirst($component);
		}

		// Trims input, normalize separators, and ensure that all paths are in Pascalcase.
		// 修剪输入，规范化分隔符，并确保所有路径都是Pascalcase。
		$class = ltrim(implode('\\', array_map('pascalize', explode('\\', str_replace('/', '\\', trim($class))))), '\\/');

		// Gets the namespace from input.
		// 从输入获取名称空间
		$namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');

		if (strncmp($class, $namespace, strlen($namespace)) === 0)
		{
			return $class; // @codeCoverageIgnore
		}

		return $namespace . '\\' . $this->directory . '\\' . str_replace('/', '\\', $class);
	}

	/**
	 * Gets the generator view as defined in the `Config\Generators::$views`,
	 * with fallback to `$template` when the defined view does not exist.
	 *
	 * @param array $data Data to be passed to the view.
	 *
	 * @return string
	 */
	protected function renderTemplate(array $data = []): string
	{
		try
		{
			return view(config('Generators')->views[$this->name], $data, ['debug' => false]);
		}
		catch (Throwable $e)
		{
			log_message('error', $e->getMessage());

			return view("CodeIgniter\Commands\Generators\Views\\{$this->template}", $data, ['debug' => false]);
		}
	}

	/**
	 * Performs pseudo-variables contained within view file.
	 * 执行视图文件中包含的伪变量
	 *
	 * @param string $class
	 * @param array  $search
	 * @param array  $replace
	 * @param array  $data
	 *
	 * @return string
	 */
	protected function parseTemplate(string $class, array $search = [], array $replace = [], array $data = []): string
	{
		// Retrieves the namespace part from the fully qualified class name.
		$namespace = trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');

		array_push($search, '<@php', '{namespace}', '{class}');
		array_push($replace, '<?php', $namespace, str_replace($namespace . '\\', '', $class));

		return str_replace($search, $replace, $this->renderTemplate($data));
	}

	/**
	 * Builds the contents for class being generated, doing all
	 * the replacements necessary, and alphabetically sorts the
	 * imports for a given template.
	 * 为生成的类构建内容，完成所有必要的替换，并按字母顺序对指定模板中的导入进行排序。
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildContent(string $class): string
	{
		$template = $this->prepare($class);

		if ($this->sortImports && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match))
		{
			$imports = explode("\n", trim($match['imports']));
			sort($imports);

			return str_replace(trim($match['imports']), implode("\n", $imports), $template);
		}

		return $template;
	}

	/**
	 * Builds the file path from the class name.
	 * 从类名生成文件路径
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildPath(string $class): string
	{
		$namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');

		// Check if the namespace is actually defined and we are not just typing gibberish.
		// 检查名称空间是否确实定义了，并且我们不是在键入胡言乱语。
		$base = Services::autoloader()->getNamespace($namespace);

		if (! $base = reset($base))
		{
			// @codeCoverageIgnoreStart
			CLI::error(lang('CLI.namespaceNotDefined', [$namespace]), 'light_gray', 'red');
			CLI::newLine();

			return '';
			// @codeCoverageIgnoreEnd
		}

		$base = realpath($base) ?: $base;
		$file = $base . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim(str_replace($namespace, '', $class), '\\')) . '.php';

		return implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $file), 0, -1)) . DIRECTORY_SEPARATOR . $this->basename($file);
	}

	/**
	 * Allows child generators to modify the internal `$hasClassName` flag.
	 * 允许子生成器修改内部的‘ $hasClassName ’标志
	 *
	 * @param boolean $hasClassName
	 *
	 * @return $this
	 */
	protected function setHasClassName(bool $hasClassName)
	{
		$this->hasClassName = $hasClassName;

		return $this;
	}

	/**
	 * Allows child generators to modify the internal `$sortImports` flag.
	 * 允许子生成器修改内部的‘ $sortImports ’标志
	 *
	 * @param boolean $sortImports
	 *
	 * @return $this
	 */
	protected function setSortImports(bool $sortImports)
	{
		$this->sortImports = $sortImports;

		return $this;
	}

	/**
	 * Gets a single command-line option. Returns TRUE if the option exists,
	 * but doesn't have a value, and is simply acting as a flag.
	 * 获取一个单命令行选项。如果选项存在,返回TRUE,但没有值,只是充当标志。
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getOption(string $name)
	{
		if (! array_key_exists($name, $this->params))
		{
			return CLI::getOption($name);
		}

		return is_null($this->params[$name]) ? true : $this->params[$name];
	}
}
