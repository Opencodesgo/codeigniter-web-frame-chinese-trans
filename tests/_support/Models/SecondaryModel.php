<?php namespace Tests\Support\Models;

/**
 * 测试，支持，模型，二次模型
 */

use CodeIgniter\Model;

class SecondaryModel extends Model
{
	protected $table = 'secondary';

	protected $primaryKey = 'id';

	protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'int';

	protected $allowedFields = [
		'key',
		'value',
	];
}
