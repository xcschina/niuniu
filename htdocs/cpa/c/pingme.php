<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
$cpa_id = paramUtils::strByGET("adid");
$click_id = paramUtils::strByGET("click_id");
$android_id = paramUtils::strByGET("android_id");
$imei = paramUtils::strByGET("imei");
$android_id_md5 = paramUtils::strByGET("md5");
$click_time = paramUtils::strByGET("click_time");
$ip = paramUtils::strByGET("ip");
$us = paramUtils::strByGET("us");
$data = array(
    'cpa_id'=>$cpa_id,
    'click_id'=>$click_id,
    'sub_pub_id'=>$sub_pub_id,
    'click_time'=>$click_time,
    'ip'=>$ip,
    'us'=>$us,
    'android_id'=>$android_id,
    'android_id_md5'=>$android_id_md5,
    'imei'=>$imei,
);
BO('clickServ');
$bo = new clickServ();
$bo->android_param_ver($cpa_id,$data);
$url = 'https://apk.66173.cn/6024/xsjmyhf_pingme.apk';
$bo->vido_device_click($data,$url);
die(json_encode(array('status'=>0)));

