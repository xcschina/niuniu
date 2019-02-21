<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$nt_data = paramUtils::strByPOST("nt_data");
$md5Sign = paramUtils::strByPOST("md5Sign");
$sign = paramUtils::strByPOST("sign");
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'super_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key'] || !$ch_app_info['param1'] || !$ch_app_info['param2']){
    die("缺少必要参数。");
}
$callbackkey = $ch_app_info['param1'];
$md5key = $ch_app_info['param2'];
$QuickEncrypt = new QuickEncrypt();

$localSign = $QuickEncrypt->getSign($_POST,$md5key);
//核对签名
if($_POST['md5Sign'] !== $localSign){
    die('签名错误');
}
//解密xml
$asyContent = $QuickEncrypt->decode($_POST['nt_data'],$callbackkey);
//解析xml
$xmlObject = simplexml_load_string($asyContent);
$message = $xmlObject->message;
$status = $message->status;
$attach = $message->game_order;
$paytime = strtotime($message->pay_time);
$orderid = $message->order_no;

if($status=='0' || !$attach || !$orderid || !$pay_time){
    $order_info = $DAO->get_super_order($attach);
    if(empty($order_info['product_id']) || empty($order_info)){
        die("error");
    }
    $super_info = $DAO->get_super_info($order_info['app_id']);
    if(!$super_info['app_key']){
        die("error");
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($attach,$paytime,$orderid);
    }
    die("SUCCESS");
}else{
    die("errorSign");
}