<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay_web');

$bo = new android_pay_web();
$bo->update_order_moeny();