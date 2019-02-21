<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore','paramUtils');

$bo = new baseCore();
$data = json_decode(json_encode(simplexml_load_string($GLOBALS["HTTP_RAW_POST_DATA"], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
$bo->err_log($bo->client_ip()."\r".var_export($data,1),'sdk_qpay_notify');
BO('android_pay');
$bo = new android_pay();
if($data['trade_state']=='SUCCESS'){
    $bo->sdk_qpay_notify($data);
}else{
    $bo->err_log($data,'sdk_qpay_notify');
    echo "fail";
}
?>