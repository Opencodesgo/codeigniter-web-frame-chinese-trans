<?php
/**
 * 控制器，主页
 */

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
}
