<?php namespace Tests\Support\Models;

/**
 * 测试，支持，模型，用户模型
 */

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table = 'user';

	protected $allowedFields = [
		'name',
		'email',
		'country',
		'deleted_at',
	];

	protected $returnType = 'object';

	protected $useSoftDeletes = true;

	protected $dateFormat = 'datetime';

	public $name = '';

	public $email = '';

	public $country = '';
}
