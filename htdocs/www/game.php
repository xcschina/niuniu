<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
//BO('game_web');
//$id = paramUtils::intByGET("id",false);
//$bo = new game_web($id);
//$bo->game_view();

$id  = paramUtils::intByGET("id",false);
$act = paramUtils::strByGET("act",true);
$acts = array(
	'game'    => array('down', 'qr'),
	'product' => array('character','recharge', 'topup', 'account', 'props', 'coin')
	);

if(in_array($act, $acts['game'])){
	BO('game_web');
	$bo = new game_web($id);
	switch ($act) {
		case 'down': 
			$bo->game_ch_download();
			break;
		case 'qr':
			$down_id = paramUtils::intByGET("did",true);
			$bo->get_game_qrcode($down_id);
			break;
		default: break;
	}
}else{
	BO('product_web');
	$bo = new product_web();
	if(in_array($act, $acts['product'])){
		switch ($act) {
			case 'character': $type = 1; break; // 首充
			case 'recharge':  $type = 2; break; // 续充
			case 'topup':     $type = 3; break; // 代充
			case 'account':   $type = 4; break; // 账号
			case 'props':     					// 装备
			case 'coin': 	  $type = 5; break; // 游戏币
			default: 	 	  $type = 0; break;
		}
		$bo->buy_view($id,$type);
	}else{
		$bo->buy_view($id);
	}
}








