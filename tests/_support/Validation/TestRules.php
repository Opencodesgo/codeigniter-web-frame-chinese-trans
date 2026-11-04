<?php namespace Tests\Support\Validation;

/**
 * 测试，支持，验证，TestRules
 */

class TestRules {

	public function customError(string $str, string &$error = null)
	{
		$error = 'My lovely error';

		return false;
	}

	//--------------------------------------------------------------------

}
