<?php
/**
 * CodeIgniter，测试，模拟，模拟语言
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

use CodeIgniter\Language\Language;

class MockLanguage extends Language
{
	/**
	 * Stores the data that should be
	 * returned by the 'requireFile()' method.
	 * 存储由 'requireFile()' 方法应返回的数据
	 *
	 * @var mixed
	 */
	protected $data;

	//--------------------------------------------------------------------

	/**
	 * Sets the data that should be returned by the
	 * 'requireFile()' method to allow easy overrides
	 * during testing.
	 *
	 * @param array       $data
	 * @param string      $file
	 * @param string|null $locale
	 *
	 * @return $this
	 */
	public function setData(string $file, array $data, string $locale = null)
	{
		$this->language[$locale ?? $this->locale][$file] = $data;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides an override that allows us to set custom
	 * data to be returned easily during testing.
	 * 提供了一个覆盖功能，使我们能够在测试期间轻松设置自定义数据以返回。
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	protected function requireFile(string $path): array
	{
		return $this->data ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Arbitrarily turnoff internationalization support for testing
	 * 任意关闭对测试的国际化支持
	 */
	public function disableIntlSupport()
	{
		$this->intlSupport = false;
	}
}
