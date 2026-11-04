<?php
/**
 * 测试，支持，数据库，播种，另一种播种机
 */

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnotherSeeder extends Seeder
{
	public function run()
	{
		$row = [
			'name'    => 'Jerome Lohan',
			'email'   => 'jlo@lohanenterprises.com',
			'country' => 'UK',
		];

		$this->db->table('user')->insert($row);
	}
}
