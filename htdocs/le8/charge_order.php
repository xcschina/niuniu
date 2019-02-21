<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 16/9/22
 * Time: 18:54
 * 乐吧业务回调
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");

$bo = new le8_pay_web();
$bo->charge_order();

//die("用户ID是".$user_id);
//$sign = "";
//$url = "http://hs.le890.com/api.php?m=order&a=front&user_id=".$user_id."&nn_user_id=".$user_id
//    ."&nn_order_id=".$trade_order_id."&pay_money=".$pay_money."&status=1&timestamp=".strtotime("now")."&sign=".$sign;