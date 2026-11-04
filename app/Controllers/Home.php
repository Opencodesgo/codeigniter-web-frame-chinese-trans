<?php
/**
 * App，控制器，首页
 */

namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
		return view('welcome_message');
	}
}
