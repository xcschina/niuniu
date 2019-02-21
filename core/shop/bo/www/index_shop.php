<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/14 >
// --------------------------------------

COMMON('baseCore', 'pageCore');
DAO('www/index_shop_dao');

class index_shop extends baseCore{

	const TPL = 'www';
	private $DAO;

	public function __construct(){
		parent::__construct();
		$this->DAO  = new index_shop_dao();
	}

	// 店铺列表页
	public function shop_list_view(){
		die('这里是www端的 店铺列表页');
		$this->display(self::TPL.'/shop_list.html');
	}

	// 店铺详情页
	public function shop_view(){

		$this->display(self::TPL.'/shop.html');
	}

}
