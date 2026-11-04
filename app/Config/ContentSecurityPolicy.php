<?php
/**
 * 配置，内容安全策略
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Stores the default settings for the ContentSecurityPolicy, if you
 * choose to use it. The values here will be read in and set as defaults
 * for the site. If needed, they can be overridden on a page-by-page basis.
 * 如果选择使用，将存储内容安全策略的默认设置。
 * 此处的值将被读取并设置为网站的默认值。如有需要，可逐页进行覆盖。
 *
 * Suggested reference for explanations:
 *
 * @see https://www.html5rocks.com/en/tutorials/security/content-security-policy/
 */
class ContentSecurityPolicy extends BaseConfig
{
	//-------------------------------------------------------------------------
	// Broadbrush CSP management	广泛的CSP管理
	//-------------------------------------------------------------------------

	/**
	 * Default CSP report context
	 * 默认的CSP报告上下文
	 *
	 * @var boolean
	 */
	public $reportOnly = false;

	/**
	 * Specifies a URL where a browser will send reports
	 * when a content security policy is violated.
	 * 指定当内容安全策略被违反时，浏览器将发送报告的URL。
	 *
	 * @var string|null
	 */
	public $reportURI = null;

	/**
	 * Instructs user agents to rewrite URL schemes, changing
	 * HTTP to HTTPS. This directive is for websites with
	 * large numbers of old URLs that need to be rewritten.
	 * 指示用户代理重写URL方案，将HTTP更改为HTTPS。
	 * 此指令适用于需要重写大量旧URL的网站。
	 *
	 * @var boolean
	 */
	public $upgradeInsecureRequests = false;

	//-------------------------------------------------------------------------
	// Sources allowed
	// Note: once you set a policy to 'none', it cannot be further restricted
	// 资源允许
	//-------------------------------------------------------------------------

	/**
	 * Will default to self if not overridden
	 * 如果未重写，将默认为self吗。
	 *
	 * @var string|string[]|null
	 */
	public $defaultSrc = null;

	/**
	 * Lists allowed scripts' URLs.
	 * 列表允许脚本的url
	 *
	 * @var string|string[]
	 */
	public $scriptSrc = 'self';

	/**
	 * Lists allowed stylesheets' URLs.
	 * 列表允许样式表的url
	 *
	 * @var string|string[]
	 */
	public $styleSrc = 'self';

	/**
	 * Defines the origins from which images can be loaded.
	 * 定义可以从何处加载图像的原点
	 *
	 * @var string|string[]
	 */
	public $imageSrc = 'self';

	/**
	 * Restricts the URLs that can appear in a page's `<base>` element.
	 * 限制可以出现在页面‘ <base> ’元素中的url。
	 *
	 * Will default to self if not overridden
	 * 如果未重写，将默认为self吗。
	 *
	 * @var string|string[]|null
	 */
	public $baseURI = null;

	/**
	 * Lists the URLs for workers and embedded frame contents
	 * 列出worker和嵌入框架内容的url
	 *
	 * @var string|string[]
	 */
	public $childSrc = 'self';

	/**
	 * Limits the origins that you can connect to (via XHR,
	 * WebSockets, and EventSource).
	 * 限制您可连接的来源（通过 XHR、WebSockets 和 EventSource）
	 *
	 * @var string|string[]
	 */
	public $connectSrc = 'self';

	/**
	 * Specifies the origins that can serve web fonts.
	 * 指定可以提供web字体的源
	 *
	 * @var string|string[]
	 */
	public $fontSrc = null;

	/**
	 * Lists valid endpoints for submission from `<form>` tags.
	 * 列出从‘ <form> ’标签提交的有效端点
	 *
	 * @var string|string[]
	 */
	public $formAction = 'self';

	/**
	 * Specifies the sources that can embed the current page.
	 * This directive applies to `<frame>`, `<iframe>`, `<embed>`,
	 * and `<applet>` tags. This directive can't be used in
	 * `<meta>` tags and applies only to non-HTML resources.
	 * 指定可以嵌入当前页面的源。
	 *
	 * @var string|string[]|null
	 */
	public $frameAncestors = null;

	/**
	 * Restricts the origins allowed to deliver video and audio.
	 * 限制允许发送视频和音频的来源
	 *
	 * @var string|string[]|null
	 */
	public $mediaSrc = null;

	/**
	 * Allows control over Flash and other plugins.
	 * 允许控制Flash和其他插件
	 *
	 * @var string|string[]
	 */
	public $objectSrc = 'self';

	/**
	 * @var string|string[]|null
	 */
	public $manifestSrc = null;

	/**
	 * Limits the kinds of plugins a page may invoke.
	 * 限制一个页面可以调用的插件种类
	 *
	 * @var string|string[]|null
	 */
	public $pluginTypes = null;

	/**
	 * List of actions allowed.
	 * 允许的操作列表
	 *
	 * @var string|string[]|null
	 */
	public $sandbox = null;
}
