<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","On");
error_reporting(E_ALL);
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
    case"game_template":
        $id = paramUtils::intByREQUEST("id", false);
        $bo->game_template($id);
        break;
    case'general':
        if($id=='1009'){
            die("该页面已被管理员下架");
        }
        $bo->general_view($id);
        break;
    case'general_down':
        $bo->general_down($id);
        break;
    case'cqzx':
        $bo->cqzx();
        break;
    case'cqll':
        $bo->cqll();
        break;
    case'cqll2':
        $bo->cqll2();
        break;
    case'cqsb':
        $bo->cqsb();
        break;
    case'cqtt':
        $bo->cqtt();
        break;
    case'cqwb':
        $bo->cqwb();
        break;
    case'cqtl':
        $bo->cqtl();
        break;
}