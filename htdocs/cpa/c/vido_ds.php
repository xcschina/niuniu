<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
$cpa_id = paramUtils::strByGET("adid");
$click_id = paramUtils::strByGET("click_id");
$android_id_md5 = paramUtils::strByGET("md5");
$imei = paramUtils::strByGET("imei");
$sub_pub_id = paramUtils::strByGET("sub_pub_id");
$android_id = paramUtils::strByGET("android_id");

$data = array(
    'cpa_id'=>$cpa_id,
    'click_id'=>$click_id,
    'android_id'=>$android_id,
    'android_id_md5'=>$android_id_md5,
    'imei'=>$imei,
    'sub_pub_id'=>$sub_pub_id,

);
BO('clickServ');
$bo = new clickServ();
$bo->android_param_ver($cpa_id,$data);
$url = 'https://apk.66173.cn/6024/xsjmyhf_vido.apk';
$bo->no_vido_device_click($data,$url);
die(json_encode(array('status'=>0)));

