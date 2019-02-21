<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("website_web");
COMMON('paramUtils');
$bo = new website_web();
$act = paramUtils::strByGET("act",false);
$id = paramUtils::intByGET("id");
switch ($act) {
    default:
        break;
    case'zxj':
        $bo->zxj_view($id);
        break;
    case'blr':
        $bo->blr_view($id);
        break;
    case'zt3':
        $bo->zt3_view($id);
        break;
    case'fishing':
        $bo->fishing_view();
        break;
    case'blhdx':
        $bo->blhdx_view();
        break;
    case"game_info":
        $id = paramUtils::intByREQUEST("id", false);
        $bo->game_info($id);
        break;
    case'general':
        $bo->general_view($id);
        break;
    case'general_down':
        $bo->general_down($id);
        break;
    case'down_log':
        $bo->down_log();
        break;
}