<?php
/**
 * CodeIgniter，配置，服务
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

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Debug\Exceptions;
use CodeIgniter\Debug\Iterator;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Debug\Toolbar;
use CodeIgniter\Email\Email;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Format\Format;
use CodeIgniter\Honeypot\Honeypot;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Negotiate;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Images\Handlers\BaseHandler;
use CodeIgniter\Language\Language;
use CodeIgniter\Log\Logger;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Router\RouteCollectionInterface;
use CodeIgniter\Router\Router;
use CodeIgniter\Security\Security;
use CodeIgniter\Session\Session;
use CodeIgniter\Throttle\Throttler;
use CodeIgniter\Typography\Typography;
use CodeIgniter\Validation\Validation;
use CodeIgniter\View\Cell;
use CodeIgniter\View\Parser;
use CodeIgniter\View\RendererInterface;
use CodeIgniter\View\View;
use Config\App;
use Config\Cache;
use Config\Email as EmailConfig;
use Config\Encryption as EncryptionConfig;
use Config\Exceptions as ExceptionsConfig;
use Config\Filters as FiltersConfig;
use Config\Format as FormatConfig;
use Config\Honeypot as HoneypotConfig;
use Config\Images;
use Config\Migrations;
use Config\Pager as PagerConfig;
use Config\Toolbar as ToolbarConfig;
use Config\Validation as ValidationConfig;
use Config\View as ViewConfig;

/**
 * Services Configuration file.
 * 服务配置文件。
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 * 服务只是系统用于完成其功能的其他类或库。
 * CodeIgniter 使用它来使框架的核心可以轻松替换，而不会影响应用程序其余部分的使用。
 *
 * This is used in place of a Dependency Injection container primarily
 * due to its simplicity, which allows a better long-term maintenance
 * of the applications built on top of CodeIgniter. A bonus side-effect
 * is that IDEs are able to determine what class you are calling
 * whereas with DI Containers there usually isn't a way for them to do this.
 *
 * @see http://blog.ircmaxell.com/2015/11/simple-easy-risk-and-change.html
 * @see http://www.infoq.com/presentations/Simple-Made-Easy
 */
class Services extends BaseService
{
	/**
	 * The cache class provides a simple way to store and retrieve
	 * complex data for later.
	 * 缓存类提供了一种简单的方法，用于存储和检索复杂数据以备后续使用。
	 *
	 * @param Cache|null $config
	 * @param boolean    $getShared
	 *
	 * @return CacheInterface
	 */
	public static function cache(Cache $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('cache', $config);
		}

		$config = $config ?? new Cache();

		return CacheFactory::getHandler($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The CLI Request class provides for ways to interact with
	 * a command line request.
	 * CLI 请求类提供了与命令行请求进行交互的方法
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return CLIRequest
	 */
	public static function clirequest(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('clirequest', $config);
		}

		$config = $config ?? config('App');

		return new CLIRequest($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The commands utility for running and working with CLI commands.
	 * 用于运行和使用CLI命令的命令实用程序
	 *
	 * @param boolean $getShared
	 *
	 * @return Commands
	 */
	public static function commands(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('commands');
		}

		return new Commands();
	}

	/**
	 * The CURL Request class acts as a simple HTTP client for interacting
	 * with other servers, typically through APIs.
	 * CURL请求类充当一个简单的HTTP客户端，用于通过API与其它服务器进行交互。
	 *
	 * @param array                  $options
	 * @param ResponseInterface|null $response
	 * @param App|null               $config
	 * @param boolean                $getShared
	 *
	 * @return CURLRequest
	 */
	public static function curlrequest(array $options = [], ResponseInterface $response = null, App $config = null, bool $getShared = true)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('curlrequest', $options, $response, $config);
		}

		$config   = $config ?? config('App');
		$response = $response ?? new Response($config);

		return new CURLRequest(
			$config,
			new URI($options['base_uri'] ?? null),
			$response,
			$options
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Email class allows you to send email via mail, sendmail, SMTP.
	 * Email类允许你通过mail， sendmail， SMTP发送电子邮件。
	 *
	 * @param EmailConfig|array|null $config
	 * @param boolean                $getShared
	 *
	 * @return Email|mixed
	 */
	public static function email($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('email', $config);
		}

		if (empty($config) || ! (is_array($config) || $config instanceof EmailConfig))
		{
			$config = config('Email');
		}

		return new Email($config);
	}

	/**
	 * The Encryption class provides two-way encryption.
	 * Encryption类提供双向加密
	 *
	 * @param EncryptionConfig|null $config
	 * @param boolean               $getShared
	 *
	 * @return EncrypterInterface Encryption handler
	 */
	public static function encrypter(EncryptionConfig $config = null, $getShared = false)
	{
		if ($getShared === true)
		{
			return static::getSharedInstance('encrypter', $config);
		}

		$config     = $config ?? config('Encryption');
		$encryption = new Encryption($config);

		return $encryption->initialize($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Exceptions class holds the methods that handle:
	 * Exceptions类包含处理以下问题的方法：
	 *
	 *  - set_exception_handler
	 *  - set_error_handler
	 *  - register_shutdown_function
	 *
	 * @param ExceptionsConfig|null $config
	 * @param IncomingRequest|null  $request
	 * @param Response|null         $response
	 * @param boolean               $getShared
	 *
	 * @return Exceptions
	 */
	public static function exceptions(
		ExceptionsConfig $config = null,
		IncomingRequest $request = null,
		Response $response = null,
		bool $getShared = true
	)
	{
		if ($getShared)
		{
			return static::getSharedInstance('exceptions', $config, $request, $response);
		}

		$config   = $config ?? config('Exceptions');
		$request  = $request ?? static::request();
		$response = $response ?? static::response();

		return new Exceptions($config, $request, $response);
	}

	//--------------------------------------------------------------------

	/**
	 * Filters allow you to run tasks before and/or after a controller
	 * is executed. During before filters, the request can be modified,
	 * and actions taken based on the request, while after filters can
	 * act on or modify the response itself before it is sent to the client.
	 * 过滤器允许您在控制器执行前和/或后运行任务。在执行前的过滤器中，可以修改请求，并根据请求执行相应操作；
	 * 而在执行后的过滤器中，则可以在将响应发送给客户端之前对响应本身进行处理或修改。
	 *
	 * @param FiltersConfig|null $config
	 * @param boolean            $getShared
	 *
	 * @return Filters
	 */
	public static function filters(FiltersConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('filters', $config);
		}

		$config = $config ?? config('Filters');

		return new Filters($config, static::request(), static::response());
	}

	//--------------------------------------------------------------------

	/**
	 * The Format class is a convenient place to create Formatters.
	 * 格式类是创建格式化程序的一个方便的位置
	 *
	 * @param FormatConfig|null $config
	 * @param boolean           $getShared
	 *
	 * @return Format
	 */
	public static function format(FormatConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('format', $config);
		}

		$config = $config ?? config('Format');

		return new Format($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Honeypot provides a secret input on forms that bots should NOT
	 * fill in, providing an additional safeguard when accepting user input.
	 * 蜜罐为表单提供了一个秘密输入，机器人不应填写该字段，从而在接收用户输入时提供额外的保护。
	 *
	 * @param HoneypotConfig|null $config
	 * @param boolean             $getShared
	 *
	 * @return Honeypot
	 */
	public static function honeypot(HoneypotConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('honeypot', $config);
		}

		$config = $config ?? config('Honeypot');

		return new Honeypot($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Acts as a factory for ImageHandler classes and returns an instance
	 * of the handler. Used like Services::image()->withFile($path)->rotate(90)->save();
	 *
	 * @param string|null $handler
	 * @param Images|null $config
	 * @param boolean     $getShared
	 *
	 * @return BaseHandler
	 */
	public static function image(string $handler = null, Images $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('image', $handler, $config);
		}

		$config  = $config ?? config('Images');
		$handler = $handler ?: $config->defaultHandler;
		$class   = $config->handlers[$handler];

		return new $class($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Iterator class provides a simple way of looping over a function
	 * and timing the results and memory usage. Used when debugging and
	 * optimizing applications.
	 * 迭代器类提供了一种简单的方法来遍历函数，并对结果和内存使用情况进行计时。
	 * 在调试和优化应用程序时使用。
	 *
	 * @param boolean $getShared
	 *
	 * @return Iterator
	 */
	public static function iterator(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('iterator');
		}

		return new Iterator();
	}

	//--------------------------------------------------------------------

	/**
	 * Responsible for loading the language string translations.
	 * 负责加载语言字符串翻译
	 *
	 * @param string|null $locale
	 * @param boolean     $getShared
	 *
	 * @return Language
	 */
	public static function language(string $locale = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('language', $locale)->setLocale($locale);
		}

		// Use '?:' for empty string check
		$locale = $locale ?: static::request()->getLocale();

		return new Language($locale);
	}

	//--------------------------------------------------------------------

	/**
	 * The Logger class is a PSR-3 compatible Logging class that supports
	 * multiple handlers that process the actual logging.
	 * Logger类是一个PSR-3兼容的日志类,它支持处理实际日志记录的多个处理程序。
	 *
	 * @param boolean $getShared
	 *
	 * @return Logger
	 */
	public static function logger(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('logger');
		}

		return new Logger(config('Logger'));
	}

	//--------------------------------------------------------------------

	/**
	 * Return the appropriate Migration runner.
	 * 返回适当的迁移流
	 *
	 * @param Migrations|null          $config
	 * @param ConnectionInterface|null $db
	 * @param boolean                  $getShared
	 *
	 * @return MigrationRunner
	 */
	public static function migrations(Migrations $config = null, ConnectionInterface $db = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('migrations', $config, $db);
		}

		$config = $config ?? config('Migrations');

		return new MigrationRunner($config, $db);
	}

	//--------------------------------------------------------------------

	/**
	 * The Negotiate class provides the content negotiation features for
	 * working the request to determine correct language, encoding, charset,
	 * and more.
	 * 谈判类提供了内容谈判特性,以工作请求来确定正确的语言、编码、字符集和更多。
	 *
	 * @param RequestInterface|null $request
	 * @param boolean               $getShared
	 *
	 * @return Negotiate
	 */
	public static function negotiator(RequestInterface $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('negotiator', $request);
		}

		$request = $request ?? static::request();

		return new Negotiate($request);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the appropriate pagination handler.
	 * 返回适当的分页处理程序
	 *
	 * @param PagerConfig|null       $config
	 * @param RendererInterface|null $view
	 * @param boolean                $getShared
	 *
	 * @return Pager
	 */
	public static function pager(PagerConfig $config = null, RendererInterface $view = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('pager', $config, $view);
		}

		$config = $config ?? config('Pager');
		$view   = $view ?? static::renderer();

		return new Pager($config, $view);
	}

	//--------------------------------------------------------------------

	/**
	 * The Parser is a simple template parser.
	 * 解析器是一个简单的模板解析器
	 *
	 * @param string|null     $viewPath
	 * @param ViewConfig|null $config
	 * @param boolean         $getShared
	 *
	 * @return Parser
	 */
	public static function parser(string $viewPath = null, ViewConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('parser', $viewPath, $config);
		}

		$viewPath = $viewPath ?: config('Paths')->viewDirectory;
		$config   = $config ?? config('View');

		return new Parser($config, $viewPath, static::locator(), CI_DEBUG, static::logger());
	}

	//--------------------------------------------------------------------

	/**
	 * The Renderer class is the class that actually displays a file to the user.
	 * The default View class within CodeIgniter is intentionally simple, but this
	 * service could easily be replaced by a template engine if the user needed to.
	 * 渲染器类是向用户显示文件的类。
	 * CodeIgniter内部的默认视图类是故意简单的,但是如果用户需要的话,这个服务很容易被模板引擎替换。
	 *
	 * @param string|null     $viewPath
	 * @param ViewConfig|null $config
	 * @param boolean         $getShared
	 *
	 * @return View
	 */
	public static function renderer(string $viewPath = null, ViewConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('renderer', $viewPath, $config);
		}

		$viewPath = $viewPath ?: config('Paths')->viewDirectory;
		$config   = $config ?? config('View');

		return new View($config, $viewPath, static::locator(), CI_DEBUG, static::logger());
	}

	//--------------------------------------------------------------------

	/**
	 * The Request class models an HTTP request.
	 * Request类为HTTP请求建模
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return IncomingRequest
	 */
	public static function request(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('request', $config);
		}

		$config = $config ?? config('App');

		return new IncomingRequest(
			$config,
			static::uri(),
			'php://input',
			new UserAgent()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * The Response class models an HTTP response.
	 * Response类为HTTP响应建模
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return Response
	 */
	public static function response(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('response', $config);
		}

		$config = $config ?? config('App');

		return new Response($config);
	}

	//--------------------------------------------------------------------

	/**
	 * The Redirect class provides nice way of working with redirects.
	 * 重定向类提供了与重定向工作的好方法
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return RedirectResponse
	 */
	public static function redirectresponse(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('redirectresponse', $config);
		}

		$config   = $config ?? config('App');
		$response = new RedirectResponse($config);
		$response->setProtocolVersion(static::request()->getProtocolVersion());

		return $response;
	}

	//--------------------------------------------------------------------

	/**
	 * The Routes service is a class that allows for easily building
	 * a collection of routes.
	 * 路由服务是一个允许轻松构建路由集合的类
	 *
	 * @param boolean $getShared
	 *
	 * @return RouteCollection
	 */
	public static function routes(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('routes');
		}

		return new RouteCollection(static::locator(), config('Modules'));
	}

	//--------------------------------------------------------------------

	/**
	 * The Router class uses a RouteCollection's array of routes, and determines
	 * the correct Controller and Method to execute.
	 * 路由器类使用 RouteCollection 中的路由数组，并确定要执行的正确控制器和方法。
	 *
	 * @param RouteCollectionInterface|null $routes
	 * @param Request|null                  $request
	 * @param boolean                       $getShared
	 *
	 * @return Router
	 */
	public static function router(RouteCollectionInterface $routes = null, Request $request = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('router', $routes, $request);
		}

		$routes  = $routes ?? static::routes();
		$request = $request ?? static::request();

		return new Router($routes, $request);
	}

	//--------------------------------------------------------------------

	/**
	 * The Security class provides a few handy tools for keeping the site
	 * secure, most notably the CSRF protection tools.
	 * 安全类提供了几个用于保护网站安全的实用工具，其中最突出的是CSRF防护工具。
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return Security
	 */
	public static function security(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('security', $config);
		}

		$config = $config ?? config('Security') ?? config('App');

		return new Security($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the session manager.
	 * 返回会话管理器
	 *
	 * @param App|null $config
	 * @param boolean  $getShared
	 *
	 * @return Session
	 */
	public static function session(App $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('session', $config);
		}

		$config = $config ?? config('App');
		$logger = static::logger();

		$driverName = $config->sessionDriver;
		$driver     = new $driverName($config, static::request()->getIPAddress());
		$driver->setLogger($logger);

		$session = new Session($driver, $config);
		$session->setLogger($logger);

		if (session_status() === PHP_SESSION_NONE)
		{
			$session->start();
		}

		return $session;
	}

	//--------------------------------------------------------------------

	/**
	 * The Throttler class provides a simple method for implementing
	 * rate limiting in your applications.
	 * Throttler 类为在您的应用程序中实现速率限制提供了一种简单的方法
	 *
	 * @param boolean $getShared
	 *
	 * @return Throttler
	 */
	public static function throttler(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('throttler');
		}

		return new Throttler(static::cache());
	}

	//--------------------------------------------------------------------

	/**
	 * The Timer class provides a simple way to Benchmark portions of your
	 * application.
	 * Timer 类提供了一种简单的方法来对应用程序的某些部分进行性能测试
	 *
	 * @param boolean $getShared
	 *
	 * @return Timer
	 */
	public static function timer(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('timer');
		}

		return new Timer();
	}

	//--------------------------------------------------------------------

	/**
	 * Return the debug toolbar.
	 * 返回调试工具栏
	 *
	 * @param ToolbarConfig|null $config
	 * @param boolean            $getShared
	 *
	 * @return Toolbar
	 */
	public static function toolbar(ToolbarConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('toolbar', $config);
		}

		$config = $config ?? config('Toolbar');

		return new Toolbar($config);
	}

	//--------------------------------------------------------------------

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
		if ($getShared)
		{
			return static::getSharedInstance('uri', $uri);
		}

		return new URI($uri);
	}

	//--------------------------------------------------------------------

	/**
	 * The Validation class provides tools for validating input data.
	 * Validation类提供了验证输入数据的工具
	 *
	 * @param ValidationConfig|null $config
	 * @param boolean               $getShared
	 *
	 * @return Validation
	 */
	public static function validation(ValidationConfig $config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('validation', $config);
		}

		$config = $config ?? config('Validation');

		return new Validation($config, static::renderer());
	}

	//--------------------------------------------------------------------

	/**
	 * View cells are intended to let you insert HTML into view
	 * that has been generated by any callable in the system.
	 * 视图单元格旨在让您将HTML插入到系统中任何可调用函数生成的视图中
	 *
	 * @param boolean $getShared
	 *
	 * @return Cell
	 */
	public static function viewcell(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('viewcell');
		}

		return new Cell(static::cache());
	}

	//--------------------------------------------------------------------

	/**
	 * The Typography class provides a way to format text in semantically relevant ways.
	 * Typography类提供了一种以语义相关的方式格式化文本的方法
	 *
	 * @param boolean $getShared
	 *
	 * @return Typography
	 */
	public static function typography(bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('typography');
		}

		return new Typography();
	}
}
