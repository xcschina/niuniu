<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/14 >
// --------------------------------------

COMMON('baseCore');

class index extends baseCore{

	private $classes = array('index_shop','ajax_shop','order_shop','account_shop','game_shop','product_shop');

	public function __construct(){
		parent::__construct();
	}

	public function redirect($class=null, $method=null, $params=array()){
		if(!in_array($class, $this->classes)){ die('您要访问的类不存在'); }
		// $tpl = $this->is_mobile_client() ? 'm' : 'www';
		$tpl = $this->is_mobile_client() ? 'm' : 'm'; // PC端暂用m模板
		BO($tpl.DS.$class);
		$bo = new $class();
		if(!method_exists($class, $method)){ die('您要访问的类方法不存在'); }
		$bo->$method($params);
	}

}