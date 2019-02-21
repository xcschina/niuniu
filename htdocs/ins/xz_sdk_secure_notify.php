<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore','paramUtils');

$bo = new baseCore();
$request=file_get_contents('php://input');
parse_str($request,$data);
$bo->err_log($bo->client_ip()."\r".var_export($data,1),'xz_sdk_notify');
BO('android_pay');
$bo = new android_pay();
if($data['tradeStatus']=='A001'){
    $bo->xz_sdk_notify($data);
}else{
    $bo->err_log($data,'xz_sdk_notify');
    echo "fail";
}
?>