<?php namespace Tests\Support\Models;

/**
 * 测试，支持，模型，实体模型
 */

use CodeIgniter\Model;

class EntityModel extends Model
{
	protected $table = 'job';

	protected $returnType = '\Tests\Support\Models\SimpleEntity';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'int';

	protected $deletedField = 'deleted_at';

	protected $allowedFields = [
		'name',
		'description',
		'created_at',
	];
}
