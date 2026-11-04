<?php
/**
 * 测试，支持，模型，没有自动增量模型
 */

namespace Tests\Support\Models;

use CodeIgniter\Model;

class WithoutAutoIncrementModel extends Model
{
	protected $table = 'without_auto_increment';

	protected $primaryKey = 'key';

	protected $allowedFields = [
		'key',
		'value',
	];

	protected $useAutoIncrement = false;
}
