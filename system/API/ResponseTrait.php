<?php
/**
 * CodeIgniter，API，响应特性
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\API;

use CodeIgniter\Format\FormatterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Services;

/**
 * Response trait.
 * 响应特性
 *
 * Provides common, more readable, methods to provide
 * consistent HTTP responses under a variety of common
 * situations when working as an API.
 * 提供常见且易于阅读的方法，以便在作为 API 运行时，在各种常见情况下提供一致的 HTTP 响应。
 *
 * @property IncomingRequest $request
 * @property Response        $response
 */
trait ResponseTrait
{
	/**
	 * Allows child classes to override the
	 * status code that is used in their API.
	 * 允许子类覆盖其API中使用的状态码
	 *
	 * @var array
	 */
	protected $codes = [
		'created'                   => 201,
		'deleted'                   => 200,
		'updated'                   => 200,
		'no_content'                => 204,
		'invalid_request'           => 400,
		'unsupported_response_type' => 400,
		'invalid_scope'             => 400,
		'temporarily_unavailable'   => 400,
		'invalid_grant'             => 400,
		'invalid_credentials'       => 400,
		'invalid_refresh'           => 400,
		'no_data'                   => 400,
		'invalid_data'              => 400,
		'access_denied'             => 401,
		'unauthorized'              => 401,
		'invalid_client'            => 401,
		'forbidden'                 => 403,
		'resource_not_found'        => 404,
		'not_acceptable'            => 406,
		'resource_exists'           => 409,
		'conflict'                  => 409,
		'resource_gone'             => 410,
		'payload_too_large'         => 413,
		'unsupported_media_type'    => 415,
		'too_many_requests'         => 429,
		'server_error'              => 500,
		'unsupported_grant_type'    => 501,
		'not_implemented'           => 501,
	];

	/**
	 * How to format the response data.
	 * Either 'json' or 'xml'. If blank will be
	 * determine through content negotiation.
	 * 如何格式化响应数据。可选择 'json' 或 'xml'，如果为空则通过内容协商确定。
	 *
	 * @var string
	 */
	protected $format = 'json';

	/**
	 * Current Formatter instance. This is usually set by ResponseTrait::format
	 * 当前格式化程序实例。这通常由ResponseTrait::format设置。
	 *
	 * @var FormatterInterface
	 */
	protected $formatter;

	//--------------------------------------------------------------------

	/**
	 * Provides a single, simple method to return an API response, formatted
	 * to match the requested format, with proper content-type and status code.
	 * 提供一种简单的方法，用于返回符合请求格式的 API 响应，并包含正确的内容类型和状态码。
	 *
	 * @param array|string|null $data
	 * @param integer           $status
	 * @param string            $message
	 *
	 * @return mixed
	 */
	public function respond($data = null, int $status = null, string $message = '')
	{
		// If data is null and status code not provided, exit and bail
		// 如果数据为空且未提供状态码，则退出并保释。
		if ($data === null && $status === null)
		{
			$status = 404;

			// Create the output var here in case of $this->response([]);
			$output = null;
		}
		// If data is null but status provided, keep the output empty.
		// 如果data为空，但提供了status，则保持输出为空。
		elseif ($data === null && is_numeric($status))
		{
			$output = null;
		}
		else
		{
			$status = empty($status) ? 200 : $status;
			$output = $this->format($data);
		}

		if (! is_null($output))
		{
			if ($this->format === 'json')
			{
				return $this->response->setJSON($output)->setStatusCode($status, $message);
			}

			if ($this->format === 'xml')
			{
				return $this->response->setXML($output)->setStatusCode($status, $message);
			}
		}

		return $this->response->setBody($output)->setStatusCode($status, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used for generic failures that no custom methods exist for.
	 * 用于不存在自定义方法的通用故障
	 *
	 * @param string|array $messages
	 * @param integer      $status        HTTP status code
	 * @param string|null  $code          Custom, API-specific, error code
	 * @param string       $customMessage
	 *
	 * @return mixed
	 */
	public function fail($messages, int $status = 400, string $code = null, string $customMessage = '')
	{
		if (! is_array($messages))
		{
			$messages = ['error' => $messages];
		}

		$response = [
			'status'   => $status,
			'error'    => $code ?? $status,
			'messages' => $messages,
		];

		return $this->respond($response, $status, $customMessage);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Response Helpers	响应助手
	//--------------------------------------------------------------------

	/**
	 * Used after successfully creating a new resource.
	 * 成功创建新资源后使用
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondCreated($data = null, string $message = '')
	{
		return $this->respond($data, $this->codes['created'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used after a resource has been successfully deleted.
	 * 删除资源成功后使用
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondDeleted($data = null, string $message = '')
	{
		return $this->respond($data, $this->codes['deleted'], $message);
	}

	/**
	 * Used after a resource has been successfully updated.
	 * 在资源成功更新后使用
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondUpdated($data = null, string $message = '')
	{
		return $this->respond($data, $this->codes['updated'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used after a command has been successfully executed but there is no
	 * meaningful reply to send back to the client.
	 * 在命令成功执行后使用，但没有有意义的响应要发送回客户端。
	 *
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondNoContent(string $message = 'No Content')
	{
		return $this->respond(null, $this->codes['no_content'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the client is either didn't send authorization information,
	 * or had bad authorization credentials. User is encouraged to try again
	 * with the proper information.
	 * 当客户端未发送授权信息，或授权凭证无效时使用。建议用户使用正确的信息重新尝试。
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failUnauthorized(string $description = 'Unauthorized', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['unauthorized'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when access is always denied to this resource and no amount
	 * of trying again will help.
	 * 当对此资源的访问始终被拒绝，且无论尝试多少次都无法解决时使用。
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failForbidden(string $description = 'Forbidden', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['forbidden'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when a specified resource cannot be found.
	 * 当找不到指定资源时使用
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failNotFound(string $description = 'Not Found', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['resource_not_found'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the data provided by the client cannot be validated.
	 * 当无法验证客户端提供的数据时使用
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failValidationError(string $description = 'Bad Request', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['invalid_data'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Use when trying to create a new resource and it already exists.
	 * 在尝试创建新资源且该资源已存在时使用
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failResourceExists(string $description = 'Conflict', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['resource_exists'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Use when a resource was previously deleted. This is different than
	 * Not Found, because here we know the data previously existed, but is now gone,
	 * where Not Found means we simply cannot find any information about it.
	 * 当资源之前已被删除时使用。这与“未找到”不同，因为在此处我们知道数据曾存在，但现在已不存在；
	 * 而“未找到”则表示我们无法找到任何关于该数据的信息。
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failResourceGone(string $description = 'Gone', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['resource_gone'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the user has made too many requests for the resource recently.
	 * 当用户最近对资源发出过多请求时使用
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failTooManyRequests(string $description = 'Too Many Requests', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['too_many_requests'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when there is a server error.
	 * 当出现服务器错误时使用
	 *
	 * @param string      $description The error message to show the user.
	 * @param string|null $code        A custom, API-specific, error code.
	 * @param string      $message     A custom "reason" message to return.
	 *
	 * @return Response The value of the Response's send() method.
	 */
	public function failServerError(string $description = 'Internal Server Error', string $code = null, string $message = ''): Response
	{
		return $this->fail($description, $this->codes['server_error'], $code, $message);
	}

	//--------------------------------------------------------------------
	// Utility Methods	工具方法
	//--------------------------------------------------------------------

	/**
	 * Handles formatting a response. Currently makes some heavy assumptions
	 * and needs updating! :)
	 * 负责格式化响应。目前存在一些重大假设，需要更新！:)
	 *
	 * @param string|array|null $data
	 *
	 * @return string|null
	 */
	protected function format($data = null)
	{
		// If the data is a string, there's not much we can do to it...
		// 如果数据是字符串，我们就做不了什么……
		if (is_string($data))
		{
			// The content type should be text/... and not application/...
			$contentType = $this->response->getHeaderLine('Content-Type');
			$contentType = str_replace('application/json', 'text/html', $contentType);
			$contentType = str_replace('application/', 'text/', $contentType);
			$this->response->setContentType($contentType);
			$this->format = 'html';

			return $data;
		}

		$format = Services::format();
		$mime   = "application/{$this->format}";

		// Determine correct response type through content negotiation if not explicitly declared
		// 如果没有明确声明,通过内容谈判确定正确的响应类型。
		if (empty($this->format) || ! in_array($this->format, ['json', 'xml'], true))
		{
			$mime = $this->request->negotiate('media', $format->getConfig()->supportedResponseFormats, false);
		}

		$this->response->setContentType($mime);

		// if we don't have a formatter, make one
		if (! isset($this->formatter))
		{
			// if no formatter, use the default
			$this->formatter = $format->getFormatter($mime);
		}

		if ($mime !== 'application/json')
		{
			// Recursively convert objects into associative arrays
			// Conversion not required for JSONFormatter
			// 递归地将对象转换为关联数组。JSONFormatter无需进行转换。
			$data = json_decode(json_encode($data), true);
		}

		return $this->formatter->format($data);
	}

	/**
	 * Sets the format the response should be in.
	 * 设置响应的格式
	 *
	 * @param string $format
	 *
	 * @return $this
	 */
	public function setResponseFormat(string $format = null)
	{
		$this->format = strtolower($format);

		return $this;
	}
}
