<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_web');
$bo = new index_web();

$act = paramUtils::strByGET('act',true);
switch ($act) {
	case 'down_list': 
		$bo->games_download_list(); 
		break;
	case 'game_down_url':
		$bo->game_down_url();
		break;
	default: 
		$bo->game_list();
		break;
}