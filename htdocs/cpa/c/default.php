<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
$cpa_id = paramUtils::strByGET("appid");
$mac = paramUtils::strByGET("mac");
$adtid = paramUtils::strByGET("idfa");
$callback = htmlspecialchars_decode(paramUtils::strByGET("callback"));

BO('clickServ');
$bo = new clickServ();
//if(rand(1,3)!=2){
//    die(json_encode(array('status'=>'error')));
//}
//die(json_encode(array('status'=>'error')));
$bo->param_verify($cpa_id,$mac,$adtid);
//$bo->device_click($cpa_id, $mac, $adtid, $callback);
$bo->default_device_click($cpa_id, $mac, $adtid, $callback);
die(json_encode(array('status'=>0)));

