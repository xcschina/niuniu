<?php
header("Content-Type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
require_once 'config.php';
COMMON("paramUtils");
BO('apk_web');
$act = paramUtils::strByGET("act", false);

$bo = new apk_web();
switch ($act) {
    case"top_banner":
        $bo->top_banner();
        break;
    case"fine_game":
        $bo->fine_game();
        break;
    case"fine_list":
        $bo->fine_list();
        break;
    case"tx_game":
        $bo->tx_game();
        break;
    case"tx_list":
        $bo->tx_list();
        break;
    case"game_detail":
        $game_id = paramUtils::strByGET("id", false);
        $bo->game_detail($game_id);
        break;
    case"game_gift":
        $game_id = paramUtils::strByGET("id", false);
        $bo->game_gift($game_id);
        break;
    case"activity_center":
        $bo->activity_center();
        break;
    case"nnb_num":
        $bo->nnb_num();
        break;
    case"receive_gift":
        $id = paramUtils::intByGET("id", false);
        $bo->receive_gift($id);
        break;
    case"my_gift":
        $bo->my_gifts();
        break;
    case"nn_agreement":
        $bo->nn_agreement();
        break;
    case"usage_rules":
        $bo->usage_rules();
        break;
    case"qb_rate":
        $bo->qb_rate();
        break;
    default:
        $result = array("result" => "0", "desc" => "接口参数异常。");
        die("0".base64_encode(json_encode($result)));
        break;
}