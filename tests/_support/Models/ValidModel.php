<?php namespace Tests\Support\Models;

/**
 * 测试，支持，模型，有效模型
 */

use CodeIgniter\Model;

class ValidModel extends Model
{
	protected $table = 'job';

	protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'int';

	protected $allowedFields = [
		'name',
		'description',
	];

	protected $validationRules = [
		'name'  => [
			'required',
			'min_length[3]',
		],
		'token' => 'permit_empty|in_list[{id}]',
	];

	protected $validationMessages = [
		'name' => [
			'required'   => 'You forgot to name the baby.',
			'min_length' => 'Too short, man!',
		],
	];
}
