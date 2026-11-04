<?php
/**
 * 配置，安全
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * CSRF Token Name	CSRF令牌名称
	 * --------------------------------------------------------------------------
	 *
	 * Token name for Cross Site Request Forgery protection cookie.
	 * 跨站点请求伪造保护cookie的令牌名称
	 *
	 * @var string
	 */
	public $tokenName = 'csrf_test_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Header Name		CSRF报头名称
	 * --------------------------------------------------------------------------
	 *
	 * Token name for Cross Site Request Forgery protection cookie.
	 * 跨站点请求伪造保护cookie的令牌名称
	 *
	 * @var string
	 */
	public $headerName = 'X-CSRF-TOKEN';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Cookie Name		CSRF Cookie名称
	 * --------------------------------------------------------------------------
	 *
	 * Cookie name for Cross Site Request Forgery protection cookie.
	 * 跨站点请求伪造保护Cookie的名称
	 *
	 * @var string
	 */
	 public $cookieName = 'csrf_cookie_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Expires	CSRF到期
	 * --------------------------------------------------------------------------
	 *
	 * Expiration time for Cross Site Request Forgery protection cookie.
	 * 跨站请求伪造保护cookie的过期时间。
	 *
	 * Defaults to two hours (in seconds).
	 * 默认值为两个小时(秒)。
	 *
	 * @var integer
	 */
	public $expires = 7200;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Regenerate	CSRF再生
	 * --------------------------------------------------------------------------
	 *
	 * Regenerate CSRF Token on every request.
	 * 对每个请求重新生成CSRF令牌
	 *
	 * @var boolean
	 */
	public $regenerate = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Redirect	CSRF重定向
	 * --------------------------------------------------------------------------
	 *
	 * Redirect to previous page with error on failure.
	 * 重定向到上一页，错误提示失败。
	 *
	 * @var boolean
	 */
	public $redirect = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF SameSite	CSRF SameSite
	 * --------------------------------------------------------------------------
	 *
	 * Setting for CSRF SameSite cookie token.
	 * 设置CSRF SameSite cookie令牌。
	 *
	 * Allowed values are: None - Lax - Strict - ''.
	 *
	 * Defaults to `Lax` as recommended in this link:
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
       * @var string 'Lax'|'None'|'Strict'
	 */
	public $samesite = 'Lax';
}
