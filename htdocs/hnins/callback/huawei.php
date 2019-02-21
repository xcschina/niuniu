<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore');
BO('super_pay_web');
$bo = new super_pay_web();
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'huawei_h5_super_log');
$params = $_POST;
if(!$params){
    die(json_encode(array('result'=>3)));
}
$bo->hw_verfiy($params);

