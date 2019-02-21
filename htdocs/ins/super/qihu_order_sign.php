<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore');
BO('super_api_web');
$bo = new super_api_web();
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'qihu_order_sign');
$params = $_POST;
if(empty($params) || !is_array($params)){
    die('0'.base64_encode(json_encode(array('result'=>'2','desc'=>'缺少必要参数'))));
}
$bo->qihu_order_sign($params);

