<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("my_web");
COMMON('paramUtils');
$bo = new my_web();
$act = paramUtils::strByGET("act",false);
$id=paramUtils::intByGET("id");
switch ($act) {
    case'my_orders':
        $bo->my_orders();
        break;
    case'order_pay':
        $bo->order_pay($id);
        break;
    case 'order_cancel':
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
    case'my_coupon':
        $bo->my_coupon();
        break;
    case'order_detail':
        $bo->order_detail($id);
        break;
}