<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('app_web');
$act = paramUtils::strByGET("act", false);
$bo = new app_web();
switch ($act){
    case "game_ch":
        $game_id = paramUtils::intByGET("game_id", false);
        $bo->game_ch_view($game_id);
        break;
    case "sel_goods":
        $game_id = paramUtils::strByGET("game_id", false);
        $ch = paramUtils::strByGET("ch", false);
        $bo->sel_goods_view($ch,$game_id);
        break;
    case "pay_view":
        $bo->pay_view();
        break;
    case "search_ser":
        $game_id = paramUtils::strByGET("game_id", false);
        $ch = paramUtils::strByGET("ch_id", false);
        $bo->search_ser($game_id,$ch);
        break;
    case "search_user":
        $game_user = paramUtils::strByGET("game_user", false);
        $bo->search_user($game_user);
        break;
    case "recharge":
        $game_user = paramUtils::strByGET("game_user", false);
        $bo->recharge_view($game_user);
        break;
    case "go_pay":
        $bo->go_pay();
        break;
    case "go_recharge":
        $bo->go_recharge();
        break;
    default:
        break;
}