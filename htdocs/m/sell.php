<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON("paramUtils");
BO('sell_mobile');

$bo = new sell_mobile();
$game_id = paramUtils::strByGET("id",false);
$act = paramUtils::strByGET("act");

if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    switch ($act) {
        default:
            $bo->sell_account_view($game_id);
            break;
        case "props":
            $bo->sell_props_view($game_id);
            break;
        case "coin":
            $bo->sell_coin_view($game_id);
            break;
        case "account":
            $bo->sell_account_view($game_id);
            break;
        case "publish":
            $bo->sell_publish($game_id);
            break;
    }
}else{
    switch ($act) {
        default:
            $bo->sell_account_view($game_id);
            break;
        case "account":
            $bo->do_account($game_id);
            break;
        case "publish":
            $bo->do_publish($game_id);
            break;
    }
}