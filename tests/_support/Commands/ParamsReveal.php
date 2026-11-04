<?php
/**
 * 测试，支持，命令，参数显示
 */

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;

class ParamsReveal extends BaseCommand
{
	protected $group       = 'demo';
	protected $name        = 'reveal';
	protected $usage       = 'reveal [options] [arguments]';
	protected $description = 'Reveal params';
	public static $args;

	public function run(array $params)
	{
		static::$args = $params;
	}
}
