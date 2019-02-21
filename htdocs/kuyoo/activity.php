<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_web');
$bo = new index_web();
$act = paramUtils::strByGET("act");
switch ($act) {
    default:
        $bo->activity_web();
        break;
    case'activity_ajax':
        $bo->activity_ajax();
        break;
    case'get_my_gift':
        $bo->get_my_gift();
        break;
    case'ajax_get_gift':
        $bo->ajax_get_gift();
        break;
    case'ajax_get_coupon':
        $bo->ajax_get_coupon();
        break;
    case'show_my_coupon':
        $bo->show_my_coupon();
        break;
    case 'show_my_prize':
        $bo->show_my_prize();
        break;
    case 'check_num':
        $bo->check_num();
        break;
}
