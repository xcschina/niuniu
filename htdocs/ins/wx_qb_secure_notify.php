<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore','paramUtils');

$bo = new baseCore();
$data = json_decode(json_encode(simplexml_load_string($GLOBALS["HTTP_RAW_POST_DATA"], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
$bo->err_log($bo->client_ip()."\r".var_export($data,1),'qb_wxsdk_notify');
BO('app_pay');
$bo = new app_pay();
if($data['return_code']=='SUCCESS' && $data['result_code']=='SUCCESS'){
    $bo->qb_wx_notify($data);
}elseif($data['result_code']=='FAIL'){
    $bo->err_log($verify_result,'qb_wxsdk_notify');
    echo "fail";
}else{
    $bo->err_log($verify_result,'qb_wxsdk_notify');
    echo "fail";
}
?>