<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","off");
error_reporting(0);
COMMON('baseCore');
BO('super_api_web');
$bo = new super_api_web();
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'haiwei_order_sign');
$params = $_POST;
if(!$params || !is_array($params)){
    die("0".base64_encode(json_encode(array('result'=>'2','desc'=>'缺少必要参数'))));
}
$bo->get_huawei_order_sign($params);

