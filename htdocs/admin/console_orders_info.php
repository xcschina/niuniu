<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('console_orders_info_web');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new console_orders_info_web();
switch ($act){
    case"orders_list":
        $bo->get_orders_list();
        break;
    case"order_detail":
        $bo->get_order_detail($id);
        break;
    case'order_refund_view':
        $bo->order_refund_view($id);
        break;
    case'do_order_refund':
        $bo->do_order_refund();
        break;
    case'export':
        $bo->export();
        break;
    default:
        $bo->get_orders_list();
        break;
}