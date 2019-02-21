<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 16/9/22
 * Time: 18:54
 * 牛币支付首页
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");

$user_id = paramUtils::strByGET("user_id", false);
$pay_money = paramUtils::intByGET("pay_money", false);
$trade_order_id = paramUtils::strByGET("trade_order_id", false);
$appid = paramUtils::strByGET("appid", false);
$goods_name = paramUtils::strByGET("goods_name", false);
$timestamp = paramUtils::intByGET("timestamp", false);
$user_type = paramUtils::strByGET("user_type", false);
$nn_user_id = paramUtils::strByGET("nn_user_id", false);
$sign = paramUtils::strByGET("sign", false);

$bo = new le8_pay_web();
$bo->pay_index_view($user_id, $pay_money, $trade_order_id, $appid, $goods_name, $sign, $nn_user_id, $user_type, $timestamp);

//die("用户ID是".$user_id);
//$sign = "";
//$url = "http://hs.le890.com/api.php?m=order&a=front&user_id=".$user_id."&nn_user_id=".$user_id
//    ."&nn_order_id=".$trade_order_id."&pay_money=".$pay_money."&status=1&timestamp=".strtotime("now")."&sign=".$sign;