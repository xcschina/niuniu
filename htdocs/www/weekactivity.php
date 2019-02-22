<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("weekactivity_web");
COMMON('paramUtils');
$bo = new weekactivity_web();
$act = paramUtils::strByGET("act",false);
$id = paramUtils::intByGET("id");
switch ($act){
    default:
        $bo->activity_view();
        break;
    case'index':
        $bo->activity_view();
        break;
    case'ajax_down':
        $bo->ajax_down_list();
        break;
    case'ajax_buy':
        $bo->ajax_buy_list();
        break;
    case'topay':
        $bo->do_pay($id);
        break;
    case'ajax_order_info':
        $bo->ajax_order_info();
        break;

}