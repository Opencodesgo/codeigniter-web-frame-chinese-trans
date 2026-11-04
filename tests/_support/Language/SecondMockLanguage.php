<?php namespace Tests\Support\Language;

/**
 * 测试，支持，语言，第二种模拟语言
 */

use CodeIgniter\Language\Language;

class SecondMockLanguage extends Language
{
	//--------------------------------------------------------------------

	/**
	 * Expose the protected *load* method
	 * 暴露受保护的load方法
	 */
	public function loadem(string $file, string $locale = 'en', bool $return = false)
	{
		return $this->load($file, $locale, $return);
	}

	//--------------------------------------------------------------------

	/**
	 * Expose the loaded language files
	 * 公开加载的语言文件
	 */
	public function loaded(string $locale = 'en')
	{
		return $this->loadedFiles[$locale];
	}

}
