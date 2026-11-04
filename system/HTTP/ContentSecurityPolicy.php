<?php
/**
 * CodeIgniter，HTTP，内容安全策略
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

use Config\ContentSecurityPolicy as ContentSecurityPolicyConfig;

/**
 * Provides tools for working with the Content-Security-Policy header
 * to help defeat XSS attacks.
 * 提供用于处理内容安全策略（Content-Security-Policy）标头的工具，以帮助防范跨站脚本（XSS）攻击。
 *
 * @see http://www.w3.org/TR/CSP/
 * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
 * @see http://content-security-policy.com/
 * @see https://www.owasp.org/index.php/Content_Security_Policy
 */
class ContentSecurityPolicy
{
	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $baseURI = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $childSrc = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array
	 */
	protected $connectSrc = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $defaultSrc = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $fontSrc = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $formAction = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $frameAncestors = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $imageSrc = [];

	/**
	 * Used for security enforcement
	 * 用于安全执行
	 *
	 * @var array|string
	 */
	protected $mediaSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $objectSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $pluginTypes = [];

	/**
	 * Used for security enforcement
	 *
	 * @var string
	 */
	protected $reportURI;

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $sandbox = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $scriptSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $styleSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected $manifestSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var boolean
	 */
	protected $upgradeInsecureRequests = false;

	/**
	 * Used for security enforcement
	 *
	 * @var boolean
	 */
	protected $reportOnly = false;

	/**
	 * Used for security enforcement
	 *
	 * @var array
	 */
	protected $validSources = [
		'self',
		'none',
		'unsafe-inline',
		'unsafe-eval',
	];

	/**
	 * Used for security enforcement
	 *
	 * @var array
	 */
	protected $nonces = [];

	/**
	 * An array of header info since we have
	 * to build ourself before passing to Response.
	 * 一系列头部信息，因为我们必须先自行构建，然后再传递给响应。
	 *
	 * @var array
	 */
	protected $tempHeaders = [];

	/**
	 * An array of header info to build
	 * that should only be reported.
	 * 用于构建的头信息数组，仅应报告这些内容。
	 *
	 * @var array
	 */
	protected $reportOnlyHeaders = [];

	/**
	 * Constructor.
	 * 构造函数。
	 *
	 * Stores our default values from the Config file.
	 *
	 * @param ContentSecurityPolicyConfig $config
	 */
	public function __construct(ContentSecurityPolicyConfig $config)
	{
		foreach (get_object_vars($config) as $setting => $value)
		{
			if (property_exists($this, $setting))
			{
				$this->{$setting} = $value;
			}
		}
	}

	/**
	 * Compiles and sets the appropriate headers in the request.
	 * 在请求中编译并设置适当的标头。
	 *
	 * Should be called just prior to sending the response to the user agent.
	 *
	 * @param ResponseInterface $response
	 *
	 * @return void
	 */
	public function finalize(ResponseInterface &$response)
	{
		$this->generateNonces($response);
		$this->buildHeaders($response);
	}

	/**
	 * If TRUE, nothing will be restricted. Instead all violations will
	 * be reported to the reportURI for monitoring. This is useful when
	 * you are just starting to implement the policy, and will help
	 * determine what errors need to be addressed before you turn on
	 * all filtering.
	 * 如果为真，则不会有任何限制。相反，所有违规行为都将被报告到 reportURI 以供监控。
	 *
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function reportOnly(bool $value = true)
	{
		$this->reportOnly = $value;

		return $this;
	}

	/**
	 * Adds a new base_uri value. Can be either a URI class or a simple string.
	 * 添加一个新的base_uri值。可以是URI类，也可以是简单字符串。
	 *
	 * base_uri restricts the URLs that can appear in a page’s <base> element.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-base-uri
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addBaseURI($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'baseURI', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 * 为表单的行动添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * child-src lists the URLs for workers and embedded frame contents.
	 * For example: child-src https://youtube.com would enable embedding
	 * videos from YouTube but not from other origins.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-child-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addChildSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'childSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 * 为表单的行动添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * connect-src limits the origins to which you can connect
	 * (via XHR, WebSockets, and EventSource).
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addConnectSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'connectSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 * 为表单的行动添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * default_src is the URI that is used for many of the settings when
	 * no other source has been set.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-default-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function setDefaultSrc($uri, bool $explicitReporting = null)
	{
		$this->defaultSrc = [(string) $uri => $explicitReporting ?? $this->reportOnly];

		return $this;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 * 为表单的行动添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * font-src specifies the origins that can serve web fonts.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-font-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addFontSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'fontSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 * 为表单的行动添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-form-action
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addFormAction($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'formAction', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new resource that should allow embedding the resource using
	 * <frame>, <iframe>, <object>, <embed>, or <applet>
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addFrameAncestor($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'frameAncestors', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for valid image sources. Can be either
	 * a URI class or a simple string.
	 * 为有效的图像源添加一个新的有效端点。可以是URI类或简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-img-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addImageSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'imageSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for valid video and audio. Can be either
	 * a URI class or a simple string.
	 * 为有效的视频和音频添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-media-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addMediaSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'mediaSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for manifest sources. Can be either
	 * a URI class or simple string.
	 * 为清单源添加一个新的有效端点。可以是URI类或简单的字符串。
	 *
	 * @see https://www.w3.org/TR/CSP/#directive-manifest-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addManifestSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'manifestSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for Flash and other plugin sources. Can be either
	 * a URI class or a simple string.
	 * 为Flash及其他插件源添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-object-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addObjectSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'objectSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Limits the types of plugins that can be used. Can be either
	 * a URI class or a simple string.
	 * 限制可使用的插件类型。可以是URI类，也可以是简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-plugin-types
	 *
	 * @param string|array $mime              One or more plugin mime types, separate by spaces
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addPluginType($mime, bool $explicitReporting = null)
	{
		$this->addOption($mime, 'pluginTypes', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Specifies a URL where a browser will send reports when a content
	 * security policy is violated. Can be either a URI class or a simple string.
	 * 指定当内容安全策略被违反时，浏览器应发送报告的URL。可以是URI类或简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-report-uri
	 *
	 * @param string $uri
	 *
	 * @return $this
	 */
	public function setReportURI(string $uri)
	{
		$this->reportURI = $uri;

		return $this;
	}

	/**
	 * specifies an HTML sandbox policy that the user agent applies to
	 * the protected resource.
	 * 指定用户代理对受保护资源应用的HTML沙箱策略
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-sandbox
	 *
	 * @param string|array $flags             An array of sandbox flags that can be added to the directive.
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addSandbox($flags, bool $explicitReporting = null)
	{
		$this->addOption($flags, 'sandbox', $explicitReporting ?? $this->reportOnly);
		return $this;
	}

	/**
	 * Adds a new valid endpoint for javascript file sources. Can be either
	 * a URI class or a simple string.
	 * 为 JavaScript 文件源添加一个新的有效端点。可以是 URI 类型，也可以是简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addScriptSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'scriptSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Adds a new valid endpoint for CSS file sources. Can be either
	 * a URI class or a simple string.
	 * 为CSS文件源添加一个新的有效端点。可以是URI类，也可以是简单的字符串。
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public function addStyleSrc($uri, bool $explicitReporting = null)
	{
		$this->addOption($uri, 'styleSrc', $explicitReporting ?? $this->reportOnly);

		return $this;
	}

	/**
	 * Sets whether the user agents should rewrite URL schemes, changing
	 * HTTP to HTTPS.
	 * 设置用户代理是否应重写URL方案，将HTTP更改为HTTPS。
	 *
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function upgradeInsecureRequests(bool $value = true)
	{
		$this->upgradeInsecureRequests = $value;

		return $this;
	}

	//-------------------------------------------------------------------------
	// Utility	实用程序
	//-------------------------------------------------------------------------

	/**
	 * DRY method to add an string or array to a class property.
	 *
	 * @param string|array $options
	 * @param string       $target
	 * @param boolean|null $explicitReporting
	 *
	 * @return void
	 */
	protected function addOption($options, string $target, bool $explicitReporting = null)
	{
		// Ensure we have an array to work with...
		if (is_string($this->{$target}))
		{
			$this->{$target} = [$this->{$target}];
		}

		if (is_array($options))
		{
			foreach ($options as $opt)
			{
				$this->{$target}[$opt] = $explicitReporting ?? $this->reportOnly;
			}
		}
		else
		{
			$this->{$target}[$options] = $explicitReporting ?? $this->reportOnly;
		}
	}

	/**
	 * Scans the body of the request message and replaces any nonce
	 * placeholders with actual nonces, that we'll then add to our
	 * headers.
	 * 扫描请求消息的主体内容，将任何占位符 nonce 替换为实际的 nonce，之后我们再将其添加到请求头中。
	 *
	 * @param ResponseInterface $response
	 *
	 * @return void
	 */
	protected function generateNonces(ResponseInterface &$response)
	{
		$body = $response->getBody();

		if (empty($body))
		{
			return;
		}

		if (! is_array($this->styleSrc))
		{
			$this->styleSrc = [$this->styleSrc];
		}

		if (! is_array($this->scriptSrc))
		{
			$this->scriptSrc = [$this->scriptSrc];
		}

		// Replace style placeholders with nonces
		$body = preg_replace_callback('/{csp-style-nonce}/', function ($matches) {
			$nonce = bin2hex(random_bytes(12));

			$this->styleSrc[] = 'nonce-' . $nonce;

			return "nonce=\"{$nonce}\"";
		}, $body);

		// Replace script placeholders with nonces
		$body = preg_replace_callback('/{csp-script-nonce}/', function ($matches) {
			$nonce = bin2hex(random_bytes(12));

			$this->scriptSrc[] = 'nonce-' . $nonce;

			return "nonce=\"{$nonce}\"";
		}, $body);

		$response->setBody($body);
	}

	/**
	 * Based on the current state of the elements, will add the appropriate
	 * Content-Security-Policy and Content-Security-Policy-Report-Only headers
	 * with their values to the response object.
	 * 根据当前元素的状态，将添加相应的 Content-Security-Policy 和 Content-Security-Policy-Report-Only 头部及其值到响应对象中。
	 *
	 * @param ResponseInterface $response
	 *
	 * @return void
	 */
	protected function buildHeaders(ResponseInterface &$response)
	{
		/**
		 * Ensure both headers are available and arrays...
		 *
		 * @var Response $response
		 */
		$response->setHeader('Content-Security-Policy', []);
		$response->setHeader('Content-Security-Policy-Report-Only', []);

		$directives = [
			'base-uri'        => 'baseURI',
			'child-src'       => 'childSrc',
			'connect-src'     => 'connectSrc',
			'default-src'     => 'defaultSrc',
			'font-src'        => 'fontSrc',
			'form-action'     => 'formAction',
			'frame-ancestors' => 'frameAncestors',
			'img-src'         => 'imageSrc',
			'media-src'       => 'mediaSrc',
			'object-src'      => 'objectSrc',
			'plugin-types'    => 'pluginTypes',
			'script-src'      => 'scriptSrc',
			'style-src'       => 'styleSrc',
			'manifest-src'    => 'manifestSrc',
			'sandbox'         => 'sandbox',
			'report-uri'      => 'reportURI',
		];

		// inject default base & default URIs if needed
		if (empty($this->baseURI))
		{
			$this->baseURI = 'self';
		}

		if (empty($this->defaultSrc))
		{
			$this->defaultSrc = 'self';
		}

		foreach ($directives as $name => $property)
		{
			if (! empty($this->{$property}))
			{
				$this->addToHeader($name, $this->{$property});
			}
		}

		// Compile our own header strings here since if we just
		// append it to the response, it will be joined with
		// commas, not semi-colons as we need.
		if (! empty($this->tempHeaders))
		{
			$header = '';

			foreach ($this->tempHeaders as $name => $value)
			{
				$header .= " {$name} {$value};";
			}

			// add token only if needed
			if ($this->upgradeInsecureRequests)
			{
				$header .= ' upgrade-insecure-requests;';
			}

			$response->appendHeader('Content-Security-Policy', $header);
		}

		if (! empty($this->reportOnlyHeaders))
		{
			$header = '';

			foreach ($this->reportOnlyHeaders as $name => $value)
			{
				$header .= " {$name} {$value};";
			}

			$response->appendHeader('Content-Security-Policy-Report-Only', $header);
		}

		$this->tempHeaders       = [];
		$this->reportOnlyHeaders = [];
	}

	/**
	 * Adds a directive and it's options to the appropriate header. The $values
	 * array might have options that are geared toward either the regular or the
	 * reportOnly header, since it's viable to have both simultaneously.
	 * 将指令及其选项添加到相应的标头中。
	 * $values 数组中的选项可能针对常规标头或仅报告标头而设置，因为同时使用两者是可行的。
	 *
	 * @param string            $name
	 * @param array|string|null $values
	 *
	 * @return void
	 */
	protected function addToHeader(string $name, $values = null)
	{
		if (is_string($values))
		{
			$values = [$values => 0];
		}

		$sources       = [];
		$reportSources = [];

		foreach ($values as $value => $reportOnly)
		{
			if (is_numeric($value) && is_string($reportOnly) && ! empty($reportOnly))
			{
				$value      = $reportOnly;
				$reportOnly = 0;
			}

			if ($reportOnly === true)
			{
				$reportSources[] = in_array($value, $this->validSources, true) ? "'{$value}'" : $value;
			}
			else
			{
				if (strpos($value, 'nonce-') === 0)
				{
					$sources[] = "'{$value}'";
				}
				else
				{
					$sources[] = in_array($value, $this->validSources, true) ? "'{$value}'" : $value;
				}
			}
		}

		if (! empty($sources))
		{
			$this->tempHeaders[$name] = implode(' ', $sources);
		}

		if (! empty($reportSources))
		{
			$this->reportOnlyHeaders[$name] = implode(' ', $reportSources);
		}
	}
}
