<?php
/**
 * CodeIgniter，蜜罐，Honeypot
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Honeypot;

use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Honeypot as HoneypotConfig;

/**
 * class Honeypot
 * 蜜罐类。
 */
class Honeypot
{
	/**
	 * Our configuration.
	 * 我们的配置
	 *
	 * @var HoneypotConfig
	 */
	protected $config;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param  HoneypotConfig $config
	 * @throws HoneypotException
	 */
	public function __construct(HoneypotConfig $config)
	{
		$this->config = $config;

		if (! $this->config->hidden)
		{
			throw HoneypotException::forNoHiddenValue();
		}

		if (empty($this->config->container) || strpos($this->config->container, '{template}') === false)
		{
			$this->config->container = '<div style="display:none">{template}</div>';
		}

		if ($this->config->template === '')
		{
			throw HoneypotException::forNoTemplate();
		}

		if ($this->config->name === '')
		{
			throw HoneypotException::forNoNameField();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the request if honeypot field has data.
	 * 检查请求是否蜜罐字段有数据
	 *
	 * @param RequestInterface $request
	 */
	public function hasContent(RequestInterface $request)
	{
		return ! empty($request->getPost($this->config->name));
	}

	/**
	 * Attaches Honeypot template to response.
	 * 将蜜罐模板附加到响应中
	 *
	 * @param ResponseInterface $response
	 */
	public function attachHoneypot(ResponseInterface $response)
	{
		$prepField = $this->prepareTemplate($this->config->template);

		$body = $response->getBody();
		$body = str_ireplace('</form>', $prepField . '</form>', $body);
		$response->setBody($body);
	}

	/**
	 * Prepares the template by adding label
	 * content and field name.
	 * 通过添加标签内容和字段名称来准备模板
	 *
	 * @param  string $template
	 * @return string
	 */
	protected function prepareTemplate(string $template): string
	{
		$template = str_ireplace('{label}', $this->config->label, $template);
		$template = str_ireplace('{name}', $this->config->name, $template);

		if ($this->config->hidden)
		{
			$template = str_ireplace('{template}', $template, $this->config->container);
		}

		return $template;
	}
}
