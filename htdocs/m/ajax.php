<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("ajax_mobile");
$bo = new ajax_mobile();
//$bo->open_debug();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'servs':
        $game_id = paramUtils::intByGET("game_id", false);
        $ch_id   = paramUtils::intByGET("ch_id");
        $buy_type   = paramUtils::intByGET("buy_type");
        $bo->ajax_servs($game_id, $ch_id, $buy_type);
        break;
    case'iapservs':
        $game_id = paramUtils::intByGET("game_id", false);
        $group_id   = paramUtils::strByGET("group_id", false);
        $bo->iap_servs($game_id, $group_id);
        break;
    case'products':
        $game_id = paramUtils::intByGET("game_id", false);
        $buy_type   = paramUtils::intByGET("buy_type", false);
        $bo->ajax_products($game_id, $buy_type);
        break;
    case"channels":
        $bo->ajax_channels( );
        break;
    case "login":
        $bo->ajax_login();
        break;
    default:
        die("error");
        break;
}