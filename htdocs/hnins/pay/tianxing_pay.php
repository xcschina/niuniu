<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,User-Agent1');
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('super_h5_web');
$api = new super_h5_web();
$params = $_POST;
$api->err_log(var_export($params,1),"h5_tianxing_pay");
$result = array("result" => 0, "desc" => "网络异常");
if(empty($params)){
    die(json_encode($result));
}
if(!$params['platform'] || !$params['appId']){
    $result['desc'] ='缺少必要参数';
    die(json_encode($result));
}
$api->tianxing_pay($params);