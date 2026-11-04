<?php namespace Tests\Support\Config;

/**
 * 测试，支持，配置，不良的注册
 */

/**
 * Class BadRegistrar
 * 不良的注册类。
 *
 * Doesn't provides a basic registrar class for testing BaseConfig registration functions,
 * because it doesn't return an associative array
 * 不提供一个基本的注册类来测试 BaseConfig 注册函数，因为它不会返回关联数组。
 */

class BadRegistrar
{

	public static function RegistrarConfig()
	{
		return 'I am not worthy';
	}

}
