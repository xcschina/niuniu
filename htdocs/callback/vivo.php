<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'vivo_sdk_callback');

if(empty($super_id)){
    die("fail");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key'] || !$ch_app_info['param1']){
    die("fail,缺少必要参数。");
}
$cpId = $ch_app_info['param1'];
$post_data = $_POST;
unset($post_data['signature']);
unset($post_data['signMethod']);
ksort($post_data);
$sign_data = "";
foreach($post_data as $key => $param){
    if($param !=''){
        $sign_data = $sign_data."&".$key."=".$param;
    }
}
$sign_data = substr($sign_data, 1);
$sign = strtolower(md5($sign_data.'&'.strtolower(md5($ch_app_info['app_key']))));
if($_POST['signature'] == $sign){
    $order_info = $DAO->get_super_order($_POST['extInfo']);
    if(empty($order_info['product_id']) || empty($order_info)){
        die("fail1");
    }
    $super_info = $DAO->get_super_info($order_info['app_id']);
    if(!$super_info['app_key']){
        die("fail2");
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($_POST['extInfo'],time(),$_POST['orderNumber']);
    }
    die("success");
}else{
    die("fail3");
}