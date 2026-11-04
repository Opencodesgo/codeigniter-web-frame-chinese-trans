<?php
/**
 * 配置，App
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Base Site URL	基本网址
	 * --------------------------------------------------------------------------
	 *
	 * URL to your CodeIgniter root. Typically this will be your base URL,
	 * WITH a trailing slash:
	 * 指向你的 CodeIgniter 根目录的 URL。通常这将是你的基础 URL，后面带有一个斜杠：
	 *
	 *    http://example.com/
	 *
	 * If this is not set then CodeIgniter will try guess the protocol, domain
	 * and path to your installation. However, you should always configure this
	 * explicitly and never rely on auto-guessing, especially in production
	 * environments.
	 *
	 * @var string
	 */
	public $baseURL = 'http://localhost:8080/';

	/**
	 * --------------------------------------------------------------------------
	 * Index File	索引文件
	 * --------------------------------------------------------------------------
	 *
	 * Typically this will be your index.php file, unless you've renamed it to
	 * something else. If you are using mod_rewrite to remove the page set this
	 * variable so that it is blank.
	 * 通常这是你的 index.php 文件，除非你将其重命名为其他名称。
	 * 如果你使用 mod_rewrite 来移除页面，请将此变量设置为空。
	 *
	 * @var string
	 */
	public $indexPage = 'index.php';

	/**
	 * --------------------------------------------------------------------------
	 * URI PROTOCOL		URI协议
	 * --------------------------------------------------------------------------
	 *
	 * This item determines which getServer global should be used to retrieve the
	 * URI string.  The default setting of 'REQUEST_URI' works for most servers.
	 * If your links do not seem to work, try one of the other delicious flavors:
	 * 此项目用于确定应使用哪个 getServer 全局变量来获取 URI 字符串。默认设置为 'REQUEST_URI'，适用于大多数服务器。
	 *
	 * 'REQUEST_URI'    Uses $_SERVER['REQUEST_URI']
	 * 'QUERY_STRING'   Uses $_SERVER['QUERY_STRING']
	 * 'PATH_INFO'      Uses $_SERVER['PATH_INFO']
	 *
	 * WARNING: If you set this to 'PATH_INFO', URIs will always be URL-decoded!
	 *
	 * @var string
	 */
	public $uriProtocol = 'REQUEST_URI';

	/**
	 * --------------------------------------------------------------------------
	 * Default Locale	默认语言环境
	 * --------------------------------------------------------------------------
	 *
	 * The Locale roughly represents the language and location that your visitor
	 * is viewing the site from. It affects the language strings and other
	 * strings (like currency markers, numbers, etc), that your program
	 * should run under for this request.
	 * “区域”大致表示访问者从哪个语言和地理位置浏览网站。
	 * 它影响语言字符串和其他字符串(如货币标记、数字等),这是你的程序。
	 *
	 * @var string
	 */
	public $defaultLocale = 'en';

	/**
	 * --------------------------------------------------------------------------
	 * Negotiate Locale		谈判语言环境
	 * --------------------------------------------------------------------------
	 *
	 * If true, the current Request object will automatically determine the
	 * language to use based on the value of the Accept-Language header.
	 * 如果为真，当前的请求对象将根据 Accept-Language 头部字段的值自动确定要使用的语言。
	 *
	 * If false, no automatic detection will be performed.
	 * 如果为false，则不执行自动检测。
	 *
	 * @var boolean
	 */
	public $negotiateLocale = false;

	/**
	 * --------------------------------------------------------------------------
	 * Supported Locales	支持地区
	 * --------------------------------------------------------------------------
	 *
	 * If $negotiateLocale is true, this array lists the locales supported
	 * by the application in descending order of priority. If no match is
	 * found, the first locale will be used.
	 * 如果 $negotiateLocale 为 true，此数组将按优先级降序列出应用程序支持的区域设置。如果没有找到匹配项，则使用第一个区域设置。
	 *
	 * @var string[]
	 */
	public $supportedLocales = ['en'];

	/**
	 * --------------------------------------------------------------------------
	 * Application Timezone	应用时区
	 * --------------------------------------------------------------------------
	 *
	 * The default timezone that will be used in your application to display
	 * dates with the date helper, and can be retrieved through app_timezone()
	 * 应用程序中将使用默认时区来显示日期，可通过 app_timezone() 获取。
	 *
	 * @var string
	 */
	public $appTimezone = 'America/Chicago';

	/**
	 * --------------------------------------------------------------------------
	 * Default Character Set	默认字符集
	 * --------------------------------------------------------------------------
	 *
	 * This determines which character set is used by default in various methods
	 * that require a character set to be provided.
	 * 这决定了在需要提供字符集的各种方法中，默认使用哪个字符集。
	 *
	 * @see http://php.net/htmlspecialchars for a list of supported charsets.
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * --------------------------------------------------------------------------
	 * URI PROTOCOL	URI协议
	 * --------------------------------------------------------------------------
	 *
	 * If true, this will force every request made to this application to be
	 * made via a secure connection (HTTPS). If the incoming request is not
	 * secure, the user will be redirected to a secure version of the page
	 * and the HTTP Strict Transport Security header will be set.
	 * 如果为真，这将强制要求向该应用程序发出的所有请求都必须通过安全连接（HTTPS）进行。
	 *
	 * @var boolean
	 */
	public $forceGlobalSecureRequests = false;

	/**
	 * --------------------------------------------------------------------------
	 * Session Driver	会话的驱动
	 * --------------------------------------------------------------------------
	 *
	 * The session storage driver to use:
	 * - `CodeIgniter\Session\Handlers\FileHandler`
	 * - `CodeIgniter\Session\Handlers\DatabaseHandler`
	 * - `CodeIgniter\Session\Handlers\MemcachedHandler`
	 * - `CodeIgniter\Session\Handlers\RedisHandler`
	 *
	 * @var string
	 */
	public $sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler';

	/**
	 * --------------------------------------------------------------------------
	 * Session Cookie Name	会话Cookie名称
	 * --------------------------------------------------------------------------
	 *
	 * The session cookie name, must contain only [0-9a-z_-] characters
	 *
	 * @var string
	 */
	public $sessionCookieName = 'ci_session';

	/**
	 * --------------------------------------------------------------------------
	 * Session Expiration	会话有效期
	 * --------------------------------------------------------------------------
	 *
	 * The number of SECONDS you want the session to last.
	 * Setting to 0 (zero) means expire when the browser is closed.
	 * 你想让会议持续的几秒钟。设置为0(0)意味着当浏览器关闭时过期。
	 *
	 * @var integer
	 */
	public $sessionExpiration = 7200;

	/**
	 * --------------------------------------------------------------------------
	 * Session Save Path	会话保存路径
	 * --------------------------------------------------------------------------
	 *
	 * The location to save sessions to and is driver dependent.
	 * 保存会话的位置取决于驱动程序。
	 *
	 * For the 'files' driver, it's a path to a writable directory.
	 * WARNING: Only absolute paths are supported!
	 *
	 * For the 'database' driver, it's a table name.
	 * Please read up the manual for the format with other session drivers.
	 *
	 * IMPORTANT: You are REQUIRED to set a valid save path!
	 *
	 * @var string
	 */
	public $sessionSavePath = WRITEPATH . 'session';

	/**
	 * --------------------------------------------------------------------------
	 * Session Match IP	会话匹配IP
	 * --------------------------------------------------------------------------
	 *
	 * Whether to match the user's IP address when reading the session data.
	 * 读取会话数据时是否匹配用户的IP地址。
	 *
	 * WARNING: If you're using the database driver, don't forget to update
	 *          your session table's PRIMARY KEY when changing this setting.
	 *
	 * @var boolean
	 */
	public $sessionMatchIP = false;

	/**
	 * --------------------------------------------------------------------------
	 * Session Time to Update	要更新的会话时间
	 * --------------------------------------------------------------------------
	 *
	 * How many seconds between CI regenerating the session ID.
	 * CI重新生成会话ID之间有多少秒
	 *
	 * @var integer
	 */
	public $sessionTimeToUpdate = 300;

	/**
	 * --------------------------------------------------------------------------
	 * Session Regenerate Destroy	会话再生销毁
	 * --------------------------------------------------------------------------
	 *
	 * Whether to destroy session data associated with the old session ID
	 * when auto-regenerating the session ID. When set to FALSE, the data
	 * will be later deleted by the garbage collector.
	 * 在自动重新生成会话ID时,是否要破坏与旧会话ID相关的会话数据。
	 * 当设置为FALSE时,数据将被垃圾收集器删除。
	 *
	 * @var boolean
	 */
	public $sessionRegenerateDestroy = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Prefix	Cookie的前缀
	 * --------------------------------------------------------------------------
	 *
	 * Set a cookie name prefix if you need to avoid collisions.
	 * 如果需要避免冲突，请设置cookie名称前缀。
	 *
	 * @var string
	 */
	public $cookiePrefix = '';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Domain	跨域Cookie
	 * --------------------------------------------------------------------------
	 *
	 * Set to `.your-domain.com` for site-wide cookies.
	 *
	 * @var string
	 */
	public $cookieDomain = '';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Path	Cookie 路径
	 * --------------------------------------------------------------------------
	 *
	 * Typically will be a forward slash.
	 * 通常是正斜杠
	 *
	 * @var string
	 */
	public $cookiePath = '/';

	/**
	 * --------------------------------------------------------------------------
	 * Cookie Secure	Cookie 安全
	 * --------------------------------------------------------------------------
	 *
	 * Cookie will only be set if a secure HTTPS connection exists.
	 * 只有在存在安全的HTTPS连接时才会设置Cookie
	 *
	 * @var boolean
	 */
	public $cookieSecure = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie HTTP Only		Cookie仅HTTP
	 * --------------------------------------------------------------------------
	 *
	 * Cookie will only be accessible via HTTP(S) (no JavaScript).
	 * Cookie只能通过HTTP(S)访问（没有JavaScript）
	 *
	 * @var boolean
	 */
	public $cookieHTTPOnly = false;

	/**
	 * --------------------------------------------------------------------------
	 * Cookie SameSite	Cookie	相同站点
	 * --------------------------------------------------------------------------
	 *
	 * Configure cookie SameSite setting. Allowed values are:
	 * - None
	 * - Lax
	 * - Strict
	 * - ''
	 *
	 * Defaults to `Lax` for compatibility with modern browsers. Setting `''`
	 * (empty string) means no SameSite attribute will be set on cookies. If
	 * set to `None`, `$cookieSecure` must also be set.
	 *
       * @var string 'Lax'|'None'|'Strict'
	 */
	public $cookieSameSite = 'Lax';

	/**
	 * --------------------------------------------------------------------------
	 * Reverse Proxy IPs	反向代理Ips
	 * --------------------------------------------------------------------------
	 *
	 * If your server is behind a reverse proxy, you must whitelist the proxy
	 * IP addresses from which CodeIgniter should trust headers such as
	 * HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP in order to properly identify
	 * the visitor's IP address.
	 * 如果您的服务器位于反向代理之后，必须将代理IP地址添加到白名单中，
	 * 以便CodeIgniter能够信任诸如HTTP_X_FORWARDED_FOR和HTTP_CLIENT_IP等头部信息，从而正确识别访问者的IP地址。
	 *
	 * You can use both an array or a comma-separated list of proxy addresses,
	 * as well as specifying whole subnets. Here are a few examples:
	 *
	 * Comma-separated:	'10.0.1.200,192.168.5.0/24'
	 * Array: ['10.0.1.200', '192.168.5.0/24']
	 *
	 * @var string|string[]
	 */
	public $proxyIPs = '';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Token Name	CSRF令牌名称
	 * --------------------------------------------------------------------------
	 *
	 * The token name.
	 * 令牌名称
	 *
	 * @deprecated Use `Config\Security` $tokenName property instead of using this property.
	 *
	 * @var string
	 */
	public $CSRFTokenName = 'csrf_test_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Header Name	CSRF报头名称
	 * --------------------------------------------------------------------------
	 *
	 * The header name.
	 * 标题名称
	 *
	 * @deprecated Use `Config\Security` $headerName property instead of using this property.
	 *
	 * @var string
	 */
	public $CSRFHeaderName = 'X-CSRF-TOKEN';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Cookie Name		CSRF Cookie名称
	 * --------------------------------------------------------------------------
	 *
	 * The cookie name.
	 * Cookie名称
	 *
	 * @deprecated Use `Config\Security` $cookieName property instead of using this property.
	 *
	 * @var string
	 */
	public $CSRFCookieName = 'csrf_cookie_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Expire	CSRF到期
	 * --------------------------------------------------------------------------
	 *
	 * The number in seconds the token should expire.
	 * 令牌应该过期的秒数。
	 *
	 * @deprecated Use `Config\Security` $expire property instead of using this property.
	 *
	 * @var integer
	 */
	public $CSRFExpire = 7200;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Regenerate	CSRF再生
	 * --------------------------------------------------------------------------
	 *
	 * Regenerate token on every submission?
	 * 在每次提交时重新生成令牌？
	 *
	 * @deprecated Use `Config\Security` $regenerate property instead of using this property.
	 *
	 * @var boolean
	 */
	public $CSRFRegenerate = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Redirect	CSRF重定向
	 * --------------------------------------------------------------------------
	 *
	 * Redirect to previous page with error on failure?
	 * 重定向到上一页失败错误？
	 *
	 * @deprecated Use `Config\Security` $redirect property instead of using this property.
	 *
	 * @var boolean
	 */
	public $CSRFRedirect = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF SameSite	CSRF SameSite
	 * --------------------------------------------------------------------------
	 *
	 * Setting for CSRF SameSite cookie token. Allowed values are:
	 * - None
	 * - Lax
	 * - Strict
	 * - ''
	 *
	 * Defaults to `Lax` as recommended in this link:
	 *
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
	 * @deprecated Use `Config\Security` $samesite property instead of using this property.
	 *
	 * @var string
	 */
	public $CSRFSameSite = 'Lax';

	/**
	 * --------------------------------------------------------------------------
	 * Content Security Policy	内容安全策略
	 * --------------------------------------------------------------------------
	 *
	 * Enables the Response's Content Secure Policy to restrict the sources that
	 * can be used for images, scripts, CSS files, audio, video, etc. If enabled,
	 * the Response object will populate default values for the policy from the
	 * `ContentSecurityPolicy.php` file. Controllers can always add to those
	 * restrictions at run time.
	 * 启用响应的内容安全策略，可限制用于图像、脚本、CSS文件、音频、视频等的来源。
	 * 如果启用，Response 对象将从 `ContentSecurityPolicy.php` 文件中填充该策略的默认值。控制器可在运行时随时添加额外的限制。
	 *
	 * For a better understanding of CSP, see these documents:
	 *
	 * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
	 * @see http://www.w3.org/TR/CSP/
	 *
	 * @var boolean
	 */
	public $CSPEnabled = false;
}
