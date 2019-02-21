<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,User-Agent1');
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');
$api = new super_pay_web();
$params = $_POST;
$result = array("result" => 0, "desc" => "网络异常");
if(empty($params)){
    die(json_encode($result));
}
$api->err_log(var_export($params,1),"h5_xiaomi_order");
$params = $api->rsa_to_params($params);
$api->err_log(var_export($params,1),"h5_xiaomi_order");
$check = $api->super_params_check($params);
if($check){
    $api->set_usr_session("SUPER_ORDER_HEADER", $params);
}
try{
    $api->create_super_ch_order();
}catch (Exception $e){
    $api->err_log(var_export($params,1),"h5_xiaomi_order_error");
    $api->err_log(var_export($e,1),"h5_xiaomi_order_error");
}