<?php namespace Tests\Support\Models;

/**
 * 测试，支持，模型，Job 模型
 */

use CodeIgniter\Model;

class JobModel extends Model
{
	protected $table = 'job';

	protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'int';

	protected $allowedFields = [
		'name',
		'description',
	];

	public $name = '';

	public $description = '';
}
