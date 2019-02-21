<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","On");
BO("my_mobile");
COMMON('paramUtils');
$bo = new my_mobile();
$act = paramUtils::strByGET("act",false);
$id=paramUtils::intByGET("id");
switch ($act) {
    case'my_orders':
        $bo->my_orders();
        break;
    case'order_detail':
        $bo->order_detail($id);
        break;
    case'order_pay':
        $bo->order_pay($id);
        break;
    case'order_cancel':
        $bo->order_cancel($id);
        break;
    case'my_msgs':
        $bo->my_msgs();
        break;
    case'msg_detail':
        $bo->msg_detail($id);
        break;
    case'my_gifts':
        $bo->my_gifts();
        break;
    case 'game_down_url':
        $bo->game_down_url();
        break;
    case"qb_orders":
        $bo->qb_orders();
        break;
    case"qb_order_detail":
        $bo->qb_order_detail($id);
        break;
    case"qb_order_pay":
        $bo->qb_order_pay($id);
        break;
    case"qb_order_cancel":
        $bo->qb_order_cancel($id);
        break;
}