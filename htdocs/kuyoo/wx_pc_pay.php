<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
$act = paramUtils::strByGET("act");
if($act=='qr'){
	BO('game_web');
	$url = paramUtils::strByGET("url");
	$bo = new game_web();
	$bo->get_qrcode($url);
}else{
	BO('product_web');
	$bo = new product_web();
	$bo->wx_pay_return();
}