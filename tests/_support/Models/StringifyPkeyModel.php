<?php namespace Tests\Support\Models;

/**
 * 测试，支持，模型，对Pkey模型进行字符串化
 */

use CodeIgniter\Model;

class StringifyPkeyModel extends Model
{
	protected $table      = 'stringifypkey';
	protected $returnType = 'object';
}
