<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 16/9/22
 * Time: 18:54
 * 牛牛账户预注册
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");

$act = paramUtils::strByGET("act", false);

$bo = new le8_pay_web();
$bo->pay_index_view($user_id, $pay_money, $trade_order_id, $appid, $goods_name, $sign, $nn_user_id, $user_type, $timestamp);
switch ($act) {
    case'check':
        $bo->register_check();
        break;
    case'register':
        $bo->register();
        break;
    default:
        die("缺少参数");
        break;
}