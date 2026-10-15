<?php
/**
 * 控制器，测试
 */

class Test extends CI_Controller {
	
	public function index() {		
		echo '<pre>';
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	}
	
	public function comments() {
		echo 'Look at this!';
	}
	
}