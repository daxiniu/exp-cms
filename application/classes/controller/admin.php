<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin extends Controller_Template {

	public $template = 'admin/index';
	
	public function action_index()
	{
		// 用来测试admin views的代码
		$data['assets_admin'] = '/assets/admin/';
		$data['assets_common'] = '/assets/common/';
		$data['title'] = '大犀牛体验版CMS - 后台管理';
		
		$data['content'] = View::factory('welcome/index', array(
			'hello'=>'Hello, World!',
		));

		foreach ($data as $key => $value) {
			$this->template->$key = $value;
		}
	}

}