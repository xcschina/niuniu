<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$data['submit_time'] = paramUtils::strByPOST("submit_time");
$data['gameId'] = paramUtils::strByPOST("gameId");
$data['sign'] = paramUtils::strByPOST("sign");
$data['orderNo'] = paramUtils::strByPOST("orderNo");
$data['payMethod'] = paramUtils::strByPOST("payMethod");
$data['payMoney'] = paramUtils::strByPOST("payMoney");
$data['sdkOrderNo'] = paramUtils::strByPOST("sdkOrderNo");
$data['orderStatus'] = paramUtils::strByPOST("orderStatus");
$data['msg'] = paramUtils::strByPOST("msg");
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'yaodian_super_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key'] || !$ch_app_info['param1'] || !$ch_app_info['param2']){
    die("缺少必要参数。");
}
$key = $ch_app_info['param1'];
$params = $data;
unset($params['sign']);
sort($params);
$str = '';
foreach($params as $key => $val ){
    $str.=$key."=".$val."&";
}
$str = substr($str,0,strlen($str)-1);
$str = $str.$key;
$new_sign = strtoupper(sha1($str));
if($new_sign == $data['sign']){
//    $order_info = $DAO->get_super_order($order_id);
}
//
//if($status=='0' || !$attach || !$orderid || !$pay_time){
//    $order_info = $DAO->get_super_order($attach);
//    if(empty($order_info['product_id']) || empty($order_info)){
//        die("error");
//    }
//    $super_info = $DAO->get_super_info($order_info['app_id']);
//    if(!$super_info['app_key']){
//        die("error");
//    }
//    if($order_info['status'] != '2'){
//        $DAO->update_super_order_info($attach,$paytime,$orderid);
//    }
//    die("SUCCESS");
//}else{
//    die("errorSign");
//}