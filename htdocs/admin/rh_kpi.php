<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

COMMON("paramUtils");
BO('rh_kpi_admin');
$act = paramUtils::strByGET("act", false);
$bo = new rh_kpi_admin();
switch ($act) {
    case"index":
        $bo->index_view();
        break;
    case"idx_group_data":
        $bo->idx_group_data();
        break;
    case"idx_trend_data":
        $bo->idx_trend_data();
        break;
    case"idx_game_data":
        $bo->idx_game_data();
        break;
    case"game_hour_data":
        $bo->game_hour_data();
        break;
    case"detail":
        $appid = paramUtils::intByGET("appids", false);
        $bo->detail($appid);
        break;
    case"realtime":
        $appid = paramUtils::intByGET("appids", false);
        $bo->realtime($appid);
        break;
//    case"channel":
//        $appid = paramUtils::intByREQUEST("appids", false);
//        $bo->channel($appid);
//        break;
//    //总体统计
    case "all_channel":
        $appid = paramUtils::intByREQUEST("appids", false);
        $bo->all_channel($appid);
        break;
//    case"ch_export":
//        $appid = paramUtils::intByREQUEST("appids", false);
//        $bo->ch_export($appid);
//        break;
//    case"information":
//        $bo->information_view();
//        break;
//    default:
//        $bo->index_view();
//        break;
}