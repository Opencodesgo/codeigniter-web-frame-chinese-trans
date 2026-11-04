<?php
/**
 * CodeIgniter，测试，模拟，模拟 CURL请求
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\CURLRequest;

/**
 * Class MockCURLRequest
 * MockCURLRequest类。
 *
 * Simply allows us to not actually call cURL during the
 * test runs. Instead, we can set the desired output
 * and get back the set options.
 * 它允许我们在测试运行期间不必实际调用 cURL。
 * 相反，我们可以设置所需的输出，并获取已设定的选项。
 */
class MockCURLRequest extends CURLRequest
{
	public $curl_options;

	protected $output = '';

	//--------------------------------------------------------------------

	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}

	//--------------------------------------------------------------------

	protected function sendRequest(array $curlOptions = []): string
	{
		// Save so we can access later.
		// 保存以便我们以后访问
		$this->curl_options = $curlOptions;

		return $this->output;
	}

	//--------------------------------------------------------------------
	// for testing purposes only
	public function getBaseURI()
	{
		return $this->baseURI;
	}

	// for testing purposes only
	public function getDelay()
	{
		return $this->delay;
	}
}
