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
    case"game_template":
        $id = paramUtils::intByREQUEST("id", false);
        $bo->game_template($id);
        break;
    case'general':
        if($_SERVER['HTTP_HOST']=='www.66173.cn'){
            header("Location: http://www.66173.cn");
        }
        if($id=='1009'){
            die("该页面已被管理员下架");
        }
        $bo->general_view($id);
        break;
    case'general_down':
        $bo->general_down($id);
        break;
    case'send_code':
        $bo->send_code();
        break;
    case'yhwz':
        $bo->yhwz();
        break;
    case'reserve_gift':
        $bo->reserve_gift();
        break;
    case'article_detail':
        $id = paramUtils::intByGET("id",false);
        $bo->article_detail($id);
        break;
}