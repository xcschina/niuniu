<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');

$adid  = paramUtils::strByGET("adid",false);
$cid  = paramUtils::strByGET("cid",false);
$imei  = paramUtils::strByGET("imei");
$mac  = paramUtils::strByGET("mac");
$androidid  = paramUtils::strByGET("androidid");
$timestamp = paramUtils::strByGET("timestamp",false);
$callback_url = paramUtils::strByGET("callback_url",false);
$timestamp = substr($timestamp , 0 , 10);

BO('clickServ');
$bo = new clickServ();
$bo->add_click($adid,$cid,$imei,$mac,$androidid,$timestamp,$callback_url);
die(json_encode(array('status'=>0)));

