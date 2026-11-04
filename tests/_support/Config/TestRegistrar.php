<?php namespace Tests\Support\Config;

/**
 * 测试，支持，配置，测试注册
 */

/**
 * Class Registrar
 * 注册类。
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */

class TestRegistrar
{

	public static function RegistrarConfig()
	{
		return [
			'bar'    => [
				'first',
				'second',
			],
			'format' => 'nice',
			'fruit'  => [
				'apple',
				'banana',
			],
		];
	}

}
