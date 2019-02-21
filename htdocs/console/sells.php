<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set('display_errors', 'On');
COMMON("paramUtils","loginCheck");

BO('sell_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new sell_admin();
switch ($act){
    case'list':
        $bo->sell_list();
        break;
    case'goods_detail':
        $bo->goods_detail($id);
        break;
    case'review_logs':
        $bo->review_logs($id);
        break;
    case'do_sure':
        $bo->do_sure($id);
        break;
    case'del_view':
        $bo->del_view($id);
        break;
    case'do_del':
        $bo->do_del($id);
        break;
    default:
        $bo->sell_list();
        break;
}