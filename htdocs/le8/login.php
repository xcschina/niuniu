<?php
/**
 * Created by PhpStorm.
 * User: ong
 * Date: 16/9/22
 * Time: 18:54
 * 六六用户登入验证
 */
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");

$user_name = paramUtils::strByPOST("user_name", false);
$user_pwd = paramUtils::strByPOST("user_pwd", false);
$timestamp = paramUtils::intByPOST("timestamp", false);
$sign = paramUtils::strByPOST("sign", false);

$bo = new le8_pay_web();
$bo->do_login($user_name, $user_pwd, $timestamp, $sign);