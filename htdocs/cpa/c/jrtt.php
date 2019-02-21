<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('clickServ');
$bo = new clickServ();
$bo->err_log(var_export($_GET,1),'cpa_jrtt_click');
$cpa_id = paramUtils::strByGET("cpa_id");
$adid  = paramUtils::strByGET("adid");
$cid  = paramUtils::strByGET("cid");
$imei  = paramUtils::strByGET("imei");
$mac  = paramUtils::strByGET("mac");
$androidid  = paramUtils::strByGET("androidid");
$timestamp = paramUtils::strByGET("timestamp");
$callback_url = paramUtils::strByGET("callback_url");
$data = array(
    'adid' => $cpa_id,
    'aid' => $adid,
    'cid' => $cid,
    'imei' => $imei,
    'mac' => $mac,
    'androidid' => $androidid,
    'timestamp' => $timestamp,
    'callback_url' => $callback_url
);
$bo->err_log(var_export($data,1),'cpa_jrtt_click');
$bo->jrtt_click($data);
die(json_encode(array('status' => 0)));

