<?php
/**
 * CodeIgniter，测试，功能响应
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Assertions for a response
 * 响应的断言。
 */
class FeatureResponse extends TestCase
{
	/**
	 * The response.
	 * 响应
	 *
	 * @var \CodeIgniter\HTTP\ResponseInterface
	 */
	public $response;

	/**
	 * DOM for the body.
	 * 主体DOM
	 *
	 * @var DOMParser
	 */
	protected $domParser;

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param ResponseInterface $response
	 */
	public function __construct(ResponseInterface $response = null)
	{
		$this->response = $response;

		$body = $response->getBody();
		if (! empty($body) && is_string($body))
		{
			$this->domParser = (new DOMParser())->withString($body);
		}
	}

	//--------------------------------------------------------------------
	// Simple Response Checks	简单回应检查
	//--------------------------------------------------------------------

	/**
	 * Boils down the possible responses into a bolean valid/not-valid
	 * response type.
	 * 将可能的响应简化为布尔类型的有效/无效响应
	 *
	 * @return boolean
	 */
	public function isOK(): bool
	{
		$status = $this->response->getStatusCode();

		// Only 200 and 300 range status codes
		// are considered valid.
		// 只有200和300范围的状态码被认为是有效的
		if ($status >= 400 || $status < 200)
		{
			return false;
		}

		// Empty bodies are not considered valid, unless in redirects
		if ($status < 300 && empty($this->response->getBody()))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns whether or not the Response was a redirect or RedirectResponse
	 * 返回响应是重定向还是RedirectResponse
	 *
	 * @return boolean
	 */
	public function isRedirect(): bool
	{
		return $this->response instanceof RedirectResponse
			|| $this->response->hasHeader('Location')
			|| $this->response->hasHeader('Refresh');
	}

	/**
	 * Assert that the given response was a redirect.
	 * 断言给定的响应是重定向
	 *
	 * @throws Exception
	 */
	public function assertRedirect()
	{
		$this->assertTrue($this->isRedirect(), 'Response is not a redirect or RedirectResponse.');
	}

	/**
	 * Returns the URL set for redirection.
	 * 返回用于重定向的URL集
	 *
	 * @return string|null
	 */
	public function getRedirectUrl(): ?string
	{
		if (! $this->isRedirect())
		{
			return null;
		}

		if ($this->response->hasHeader('Location'))
		{
			return $this->response->getHeaderLine('Location');
		}

		if ($this->response->hasHeader('Refresh'))
		{
			return str_replace('0;url=', '', $this->response->getHeaderLine('Refresh'));
		}

		return null;
	}

	/**
	 * Asserts that the status is a specific value.
	 * 断言状态是一个特定的值
	 *
	 * @param integer $code
	 *
	 * @throws Exception
	 */
	public function assertStatus(int $code)
	{
		$this->assertEquals($code, $this->response->getStatusCode());
	}

	/**
	 * Asserts that the Response is considered OK.
	 * 断言响应被认为是OK的
	 *
	 * @throws Exception
	 */
	public function assertOK()
	{
		$this->assertTrue($this->isOK(), "{$this->response->getStatusCode()} is not a successful status code, or the Response has an empty body.");
	}

	//--------------------------------------------------------------------
	// Session Assertions	会话的断言
	//--------------------------------------------------------------------

	/**
	 * Asserts that an SESSION key has been set and, optionally, test it's value.
	 * 断言已设置SESSION键，并可选地测试它的值。
	 *
	 * @param string      $key
	 * @param string|null $value
	 *
	 * @throws Exception
	 */
	public function assertSessionHas(string $key, $value = null)
	{
		$this->assertTrue(array_key_exists($key, $_SESSION), "'{$key}' is not in the current \$_SESSION");

		if ($value !== null)
		{
			$this->assertEquals($value, $_SESSION[$key], "The value of '{$key}' ({$value}) does not match expected value.");
		}
	}

	/**
	 * Asserts the session is missing $key.
	 * 断言会话缺少$key
	 *
	 * @param string $key
	 *
	 * @throws Exception
	 */
	public function assertSessionMissing(string $key)
	{
		$this->assertFalse(array_key_exists($key, $_SESSION), "'{$key}' should not be present in \$_SESSION.");
	}

	//--------------------------------------------------------------------
	// Header Assertions	头断言
	//--------------------------------------------------------------------

	/**
	 * Asserts that the Response contains a specific header.
	 * 断言响应包含特定的标头
	 *
	 * @param string      $key
	 * @param string|null $value
	 *
	 * @throws Exception
	 */
	public function assertHeader(string $key, $value = null)
	{
		$this->assertTrue($this->response->hasHeader($key), "'{$key}' is not a valid Response header.");

		if ($value !== null)
		{
			$this->assertEquals($value, $this->response->getHeaderLine($key), "The value of '{$key}' header ({$this->response->getHeaderLine($key)}) does not match expected value.");
		}
	}

	/**
	 * Asserts the Response headers does not contain the specified header.
	 * 断言响应标头不包含指定的标头。
	 *
	 * @param string $key
	 *
	 * @throws Exception
	 */
	public function assertHeaderMissing(string $key)
	{
		$this->assertFalse($this->response->hasHeader($key), "'{$key}' should not be in the Response headers.");
	}

	//--------------------------------------------------------------------
	// Cookie Assertions	Cookie断言
	//--------------------------------------------------------------------

	/**
	 * Asserts that the response has the specified cookie.
	 * 断言响应具有指定的cookie
	 *
	 * @param string      $key
	 * @param string|null $value
	 * @param string      $prefix
	 *
	 * @throws Exception
	 */
	public function assertCookie(string $key, $value = null, string $prefix = '')
	{
		$this->assertTrue($this->response->hasCookie($key, $value, $prefix), "No cookie found named '{$key}'.");
	}

	/**
	 * Assert the Response does not have the specified cookie set.
	 * 断言响应没有指定的cookie集
	 *
	 * @param string $key
	 */
	public function assertCookieMissing(string $key)
	{
		$this->assertFalse($this->response->hasCookie($key), "Cookie named '{$key}' should not be set.");
	}

	/**
	 * Asserts that a cookie exists and has an expired time.
	 * 断言cookie是否存在并且已过期
	 *
	 * @param string $key
	 * @param string $prefix
	 *
	 * @throws Exception
	 */
	public function assertCookieExpired(string $key, string $prefix = '')
	{
		$this->assertTrue($this->response->hasCookie($key, null, $prefix));
		$this->assertGreaterThan(time(), $this->response->getCookie($key, $prefix)['expires']);
	}

	//--------------------------------------------------------------------
	// DomParser Assertions	DomParser断言
	//--------------------------------------------------------------------

	/**
	 * Assert that the desired text can be found in the result body.
	 * 断言可以在结果体中找到所需的文本
	 *
	 * @param string|null $search
	 * @param string|null $element
	 *
	 * @throws Exception
	 */
	public function assertSee(string $search = null, string $element = null)
	{
		$this->assertTrue($this->domParser->see($search, $element), "Do not see '{$search}' in response.");
	}

	/**
	 * Asserts that we do not see the specified text.
	 * 断言我们没有看到指定的文本
	 *
	 * @param string|null $search
	 * @param string|null $element
	 *
	 * @throws Exception
	 */
	public function assertDontSee(string $search = null, string $element = null)
	{
		$this->assertTrue($this->domParser->dontSee($search, $element), "I should not see '{$search}' in response.");
	}

	/**
	 * Assert that we see an element selected via a CSS selector.
	 * 断言我们看到了一个通过CSS选择器被选中的元素
	 *
	 * @param string $search
	 *
	 * @throws Exception
	 */
	public function assertSeeElement(string $search)
	{
		$this->assertTrue($this->domParser->seeElement($search), "Do not see element with selector '{$search} in response.'");
	}

	/**
	 * Assert that we do not see an element selected via a CSS selector.
	 * 断言我们没有看到通过CSS选择器被选中的元素
	 *
	 * @param string $search
	 *
	 * @throws Exception
	 */
	public function assertDontSeeElement(string $search)
	{
		$this->assertTrue($this->domParser->dontSeeElement($search), "I should not see an element with selector '{$search}' in response.'");
	}

	/**
	 * Assert that we see a link with the matching text and/or class.
	 * 断言我们看到了一个带有匹配文本和/或类的链接
	 *
	 * @param string      $text
	 * @param string|null $details
	 *
	 * @throws Exception
	 */
	public function assertSeeLink(string $text, string $details = null)
	{
		$this->assertTrue($this->domParser->seeLink($text, $details), "Do no see anchor tag with the text {$text} in response.");
	}

	/**
	 * Assert that we see an input with name/value.
	 * 断言我们看到一个带有name/value的输入
	 *
	 * @param string      $field
	 * @param string|null $value
	 *
	 * @throws Exception
	 */
	public function assertSeeInField(string $field, string $value = null)
	{
		$this->assertTrue($this->domParser->seeInField($field, $value), "Do no see input named {$field} with value {$value} in response.");
	}

	//--------------------------------------------------------------------
	// JSON Methods	JSON方法
	//--------------------------------------------------------------------

	/**
	 * Returns the response's body as JSON
	 * 以JSON形式返回响应的主体
	 *
	 * @return mixed|false
	 */
	public function getJSON()
	{
		$response = $this->response->getJSON();

		if (is_null($response))
		{
			return false;
		}

		return $response;
	}

	/**
	 * Test that the response contains a matching JSON fragment.
	 * 测试响应是否包含匹配的JSON片段
	 *
	 * @param array   $fragment
	 * @param boolean $strict
	 *
	 * @throws Exception
	 */
	public function assertJSONFragment(array $fragment, bool $strict = false)
	{
		$json    = json_decode($this->getJSON(), true);
		$patched = array_replace_recursive($json, $fragment);

		if ($strict)
		{
			$this->assertSame($json, $patched, 'Response does not contain a matching JSON fragment.');
		}
		else
		{
			$this->assertEquals($json, $patched, 'Response does not contain a matching JSON fragment.');
		}
	}

	/**
	 * Asserts that the JSON exactly matches the passed in data.
	 * If the value being passed in is a string, it must be a json_encoded string.
	 * 断言JSON与传入的数据完全匹配。如果传入的值是字符串，则必须是经过JSON编码的字符串。
	 *
	 * @param string|array $test
	 *
	 * @throws Exception
	 */
	public function assertJSONExact($test)
	{
		$json = $this->getJSON();

		if (is_array($test))
		{
			$test = Services::format()->getFormatter('application/json')->format($test);
		}

		$this->assertJsonStringEqualsJsonString($test, $json, 'Response does not contain matching JSON.');
	}

	//--------------------------------------------------------------------
	// XML Methods	xml描述方法
	//--------------------------------------------------------------------

	/**
	 * Returns the response' body as XML
	 * 以XML形式返回响应体
	 *
	 * @return mixed|string
	 */
	public function getXML()
	{
		return $this->response->getXML();
	}
}
