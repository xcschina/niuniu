<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();

$super_id = paramUtils::intByREQUEST("id",false);

$app_id = paramUtils::strByPOST("app_id");
$serv_id = paramUtils::strByPOST("serv_id");
$usr_id = paramUtils::strByPOST("usr_id");
$player_id = paramUtils::strByPOST("player_id");
$app_order_id = paramUtils::strByPOST("app_order_id");
$order_id = paramUtils::strByPOST("order_id");
$coin = paramUtils::strByPOST("coin");
$money = paramUtils::strByPOST("money");
$add_time = paramUtils::strByPOST("add_time");
$sign = paramUtils::strByPOST("sign");

$result = array("success"=>0,"desc"=>'网络异常！');
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'super_sdk_callback');
if(empty($super_id)){
    $result["desc"]='error1';
    die(json_encode($result));
}
$DAO = new callback_dao();

$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    $result["desc"]='error2';
    die(json_encode($result));
}

$new_str = $app_id.$serv_id.$usr_id.$player_id.$app_order_id.$coin.$money.$add_time.$ch_app_info['app_key'];
$new_sign = md5($new_str);

if($new_sign == $sign){
    $order_info = $DAO->get_super_order($app_order_id);
    if(empty($order_info['product_id']) || empty($order_info)){
        $result["desc"]='error3';
        die(json_encode($result));
    }
    $super_info = $DAO->get_super_info($order_info['app_id']);
    if(!$super_info['app_key']){
        $result["desc"]='error4';
        die(json_encode($result));
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($app_order_id,$add_time,$order_id);
    }
    $result["success"] = 1;
    $result["desc"]= $app_order_id;
    die(json_encode($result));
}else{
    $result["desc"]='errorSign';
    die(json_encode($result));
}