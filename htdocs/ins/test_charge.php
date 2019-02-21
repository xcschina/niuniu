<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');
$api = new android_pay();

$api->err_log(var_export($_POST,1),'yyq_test_charge');

if($_POST['app_order_id']){
    die(json_encode(array('success'=>1,'desc'=>$_POST['app_order_id'])));
}else{
    die(json_encode(array('success'=>0,'desc'=>'未能获取到订单号')));
}