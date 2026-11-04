<?php
/**
 * 测试，支持，命令，抽象的信息
 */

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;

abstract class AbstractInfo extends BaseCommand
{

	protected $group       = 'demo';
	protected $name        = 'app:pablo';
	protected $description = 'Displays basic application information.';

}
