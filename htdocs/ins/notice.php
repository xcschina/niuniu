<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('paramUtils');
BO('android_pay_web');

$bo = new android_pay_web();
$bo->game_notice();