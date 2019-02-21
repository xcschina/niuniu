<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","on");
COMMON('paramUtils');
BO("android_pay_web");

//$money_id = paramUtils::intByPOST("money_id",false);

$bo = new android_pay_web();
try{
    $bo->pay_to();
}catch (Exception $e){
    $bo->err_log(var_export($e,1),"android-pay");
}