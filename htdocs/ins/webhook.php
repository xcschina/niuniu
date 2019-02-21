<?php
//ini_set("display_errors","On");
//error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8");

require_once 'config.php';
COMMON('paramUtils');
BO("paypal_web");

$bo = new paypal_web();
$bodyReceived = file_get_contents('php://input'); // 获取通知的全部内容

$bo->err_log(var_export($bodyReceived,1),'paypal_webhook');
$bo->err_log(var_export($_GET,1),'paypal_webhook');
$bo->err_log(var_export($_POST,1),'paypal_webhook');
echo "访问成功".time();
