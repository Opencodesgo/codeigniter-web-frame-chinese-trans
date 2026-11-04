<?php
/**
 * CodeIgniter，调试，异常
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Exceptions as ExceptionsConfig;
use Config\Paths;
use function error_reporting;
use ErrorException;
use Throwable;

/**
 * Exceptions manager
 * 异常管理。
 */
class Exceptions
{
	use ResponseTrait;

	/**
	 * Nesting level of the output buffering mechanism
	 * 输出缓冲机制的嵌套级别
	 *
	 * @var integer
	 */
	public $ob_level;

	/**
	 * The path to the directory containing the
	 * cli and html error view directories.
	 * 包含 cli 和 html 错误视图目录的路径
	 *
	 * @var string
	 */
	protected $viewPath;

	/**
	 * Config for debug exceptions.
	 * 配置调试异常
	 *
	 * @var ExceptionsConfig
	 */
	protected $config;

	/**
	 * The incoming request.
	 * 传入的请求
	 *
	 * @var IncomingRequest
	 */
	protected $request;

	/**
	 * The outgoing response.
	 * 输出响应
	 *
	 * @var Response
	 */
	protected $response;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param ExceptionsConfig $config
	 * @param IncomingRequest  $request
	 * @param Response         $response
	 */
	public function __construct(ExceptionsConfig $config, IncomingRequest $request, Response $response)
	{
		$this->ob_level = ob_get_level();

		$this->viewPath = rtrim($config->errorViewPath, '\\/ ') . DIRECTORY_SEPARATOR;

		$this->config = $config;

		$this->request  = $request;
		$this->response = $response;
	}

	//--------------------------------------------------------------------

	/**
	 * Responsible for registering the error, exception and shutdown
	 * handling of our application.
	 * 负责注册应用程序的错误、异常和关闭处理。
	 */
	public function initialize()
	{
		//Set the Exception Handler
		set_exception_handler([$this, 'exceptionHandler']);

		// Set the Error Handler
		set_error_handler([$this, 'errorHandler']);

		// Set the handler for shutdown to catch Parse errors
		// Do we need this in PHP7?
		register_shutdown_function([$this, 'shutdownHandler']);
	}

	//--------------------------------------------------------------------

	/**
	 * Catches any uncaught errors and exceptions, including most Fatal errors
	 * (Yay PHP7!). Will log the error, display it if display_errors is on,
	 * and fire an event that allows custom actions to be taken at this point.
	 * 捕获所有未被处理的错误和异常，包括大多数致命错误（太棒了，PHP7！）。
	 * 会记录错误，如果启用了 display_errors，则显示错误，并触发一个事件，以便在此处执行自定义操作。
	 *
	 * @param Throwable $exception
	 *
	 * @codeCoverageIgnore
	 */
	public function exceptionHandler(Throwable $exception)
	{
		[
			$statusCode,
			$exitCode,
		] = $this->determineCodes($exception);

		// Log it
		if ($this->config->log === true && ! in_array($statusCode, $this->config->ignoreCodes, true))
		{
			log_message('critical', $exception->getMessage() . "\n{trace}", [
							'trace' => $exception->getTraceAsString(),
						]);
		}

		if (! is_cli())
		{
			$this->response->setStatusCode($statusCode);
			$header = "HTTP/{$this->request->getProtocolVersion()} {$this->response->getStatusCode()} {$this->response->getReason()}";
			header($header, true, $statusCode);

			if (strpos($this->request->getHeaderLine('accept'), 'text/html') === false)
			{
				$this->respond(ENVIRONMENT === 'development' ? $this->collectVars($exception, $statusCode) : '', $statusCode)->send();

				exit($exitCode);
			}
		}

		$this->render($exception, $statusCode);

		exit($exitCode);
	}

	//--------------------------------------------------------------------

	/**
	 * Even in PHP7, some errors make it through to the errorHandler, so
	 * convert these to Exceptions and let the exception handler log it and
	 * display it.
	 * 即使在 PHP7 中，某些错误仍会传递到错误处理程序，因此应将这些错误转换为异常，
	 * 并让异常处理器进行日志记录和显示。
	 *
	 * This seems to be primarily when a user triggers it with trigger_error().
	 *
	 * @param integer      $severity
	 * @param string       $message
	 * @param string|null  $file
	 * @param integer|null $line
	 *
	 * @throws ErrorException
	 */
	public function errorHandler(int $severity, string $message, string $file = null, int $line = null)
	{
		if (! (error_reporting() & $severity))
		{
			return;
		}

		// Convert it to an exception and pass it along.
		throw new ErrorException($message, 0, $severity, $file, $line);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if any errors have happened during shutdown that
	 * need to be caught and handle them.
	 * 检查在关闭过程中是否发生错误，需要捕获并处理这些错误。
	 */
	public function shutdownHandler()
	{
		$error = error_get_last();

		// If we've got an error that hasn't been displayed, then convert
		// it to an Exception and use the Exception handler to display it
		// to the user.
		// 如果出现未显示的错误，请将其转换为异常，并使用异常处理程序向用户显示该错误。
		if (! is_null($error))
		{
			// Fatal Error?
			if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true))
			{
				$this->exceptionHandler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the view to display based on the exception thrown,
	 * whether an HTTP or CLI request, etc.
	 * 根据抛出的异常确定要显示的视图，例如是 HTTP 请求还是 CLI 请求等。
	 *
	 * @param Throwable $exception
	 * @param string    $templatePath
	 *
	 * @return string       The path and filename of the view file to use
	 */
	protected function determineView(Throwable $exception, string $templatePath): string
	{
		// Production environments should have a custom exception file.
		// 生产环境应该有一个定制的异常文件
		$view         = 'production.php';
		$templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

		if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')))
		{
			$view = 'error_exception.php';
		}

		// 404 Errors
		if ($exception instanceof PageNotFoundException)
		{
			return 'error_404.php';
		}

		// Allow for custom views based upon the status code
		// 允许基于状态码的自定义视图
		if (is_file($templatePath . 'error_' . $exception->getCode() . '.php'))
		{
			return 'error_' . $exception->getCode() . '.php';
		}

		return $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Given an exception and status code will display the error to the client.
	 * 给定一个异常和状态代码，将错误显示给客户端。
	 *
	 * @param Throwable $exception
	 * @param integer   $statusCode
	 */
	protected function render(Throwable $exception, int $statusCode)
	{
		// Determine possible directories of error views
		$path    = $this->viewPath;
		$altPath = rtrim((new Paths())->viewDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR;

		$path    .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;
		$altPath .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;

		// Determine the views
		$view    = $this->determineView($exception, $path);
		$altView = $this->determineView($exception, $altPath);

		// Check if the view exists
		if (is_file($path . $view))
		{
			$viewFile = $path . $view;
		}
		elseif (is_file($altPath . $altView))
		{
			$viewFile = $altPath . $altView;
		}

		// Prepare the vars
		$vars = $this->collectVars($exception, $statusCode);
		extract($vars);

		// Render it
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_clean();
		}

		ob_start();
		include $viewFile; // @phpstan-ignore-line
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	//--------------------------------------------------------------------

	/**
	 * Gathers the variables that will be made available to the view.
	 * 收集将提供给视图的变量
	 *
	 * @param Throwable $exception
	 * @param integer   $statusCode
	 *
	 * @return array
	 */
	protected function collectVars(Throwable $exception, int $statusCode): array
	{
		return [
			'title'   => get_class($exception),
			'type'    => get_class($exception),
			'code'    => $statusCode,
			'message' => $exception->getMessage() ?? '(null)',
			'file'    => $exception->getFile(),
			'line'    => $exception->getLine(),
			'trace'   => $exception->getTrace(),
		];
	}

	/**
	 * Determines the HTTP status code and the exit status code for this request.
	 * 确定此请求的HTTP状态码和退出状态码
	 *
	 * @param Throwable $exception
	 *
	 * @return array
	 */
	protected function determineCodes(Throwable $exception): array
	{
		$statusCode = abs($exception->getCode());

		if ($statusCode < 100 || $statusCode > 599)
		{
			$exitStatus = $statusCode + EXIT__AUTO_MIN; // 9 is EXIT__AUTO_MIN
			if ($exitStatus > EXIT__AUTO_MAX) // 125 is EXIT__AUTO_MAX
			{
				$exitStatus = EXIT_ERROR; // EXIT_ERROR
			}
			$statusCode = 500;
		}
		else
		{
			$exitStatus = 1; // EXIT_ERROR
		}

		return [
			$statusCode ?: 500,
			$exitStatus,
		];
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Display Methods	显示方法
	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 * 清除路径。
	 *
	 * This makes nicer looking paths for the error output.
	 * 这为错误输出提供了更好的路径。
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function cleanPath(string $file): string
	{
		switch (true)
		{
			case strpos($file, APPPATH) === 0:
				$file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
				break;
			case strpos($file, SYSTEMPATH) === 0:
				$file = 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSTEMPATH));
				break;
			case strpos($file, FCPATH) === 0:
				$file = 'FCPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(FCPATH));
				break;
			case defined('VENDORPATH') && strpos($file, VENDORPATH) === 0:
				$file = 'VENDORPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(VENDORPATH));
				break;
		}

		return $file;
	}

	//--------------------------------------------------------------------

	/**
	 * Describes memory usage in real-world units. Intended for use
	 * with memory_get_usage, etc.
	 * 以实际单位描述内存使用情况。适用于与 memory_get_usage 等函数配合使用。
	 *
	 * @param integer $bytes
	 *
	 * @return string
	 */
	public static function describeMemory(int $bytes): string
	{
		if ($bytes < 1024)
		{
			return $bytes . 'B';
		}
		if ($bytes < 1048576)
		{
			return round($bytes / 1024, 2) . 'KB';
		}

		return round($bytes / 1048576, 2) . 'MB';
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a syntax-highlighted version of a PHP file.
	 * 创建PHP文件的语法突出显示版本
	 *
	 * @param string  $file
	 * @param integer $lineNumber
	 * @param integer $lines
	 *
	 * @return boolean|string
	 */
	public static function highlightFile(string $file, int $lineNumber, int $lines = 15)
	{
		if (empty($file) || ! is_readable($file))
		{
			return false;
		}

		// Set our highlight colors:
		if (function_exists('ini_set'))
		{
			ini_set('highlight.comment', '#767a7e; font-style: italic');
			ini_set('highlight.default', '#c7c7c7');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#f1ce61;');
			ini_set('highlight.string', '#869d6a');
		}

		try
		{
			$source = file_get_contents($file);
		}
		catch (Throwable $e)
		{
			return false;
		}

		$source = str_replace(["\r\n", "\r"], "\n", $source);
		$source = explode("\n", highlight_string($source, true));
		$source = str_replace('<br />', "\n", $source[1]);

		$source = explode("\n", str_replace("\r\n", "\n", $source));

		// Get just the part to show
		$start = $lineNumber - (int) round($lines / 2);
		$start = $start < 0 ? 0 : $start;

		// Get just the lines we need to display, while keeping line numbers...
		$source = array_splice($source, $start, $lines, true); // @phpstan-ignore-line

		// Used to format the line number in the source
		$format = '% ' . strlen(sprintf('%s', $start + $lines)) . 'd';

		$out = '';
		// Because the highlighting may have an uneven number
		// of open and close span tags on one line, we need
		// to ensure we can close them all to get the lines
		// showing correctly.
		$spans = 1;

		foreach ($source as $n => $row)
		{
			$spans += substr_count($row, '<span') - substr_count($row, '</span');

			$row = str_replace(["\r", "\n"], ['', ''], $row);

			if (($n + $start + 1) === $lineNumber)
			{
				preg_match_all('#<[^>]+>#', $row, $tags);
				$out .= sprintf("<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s", $n + $start + 1, strip_tags($row), implode('', $tags[0])
				);
			}
			else
			{
				$out .= sprintf('<span class="line"><span class="number">' . $format . '</span> %s', $n + $start + 1, $row) . "\n";
			}
		}

		if ($spans > 0)
		{
			$out .= str_repeat('</span>', $spans);
		}

		return '<pre><code>' . $out . '</code></pre>';
	}

	//--------------------------------------------------------------------
}
