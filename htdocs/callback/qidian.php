<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$data['app_id'] = paramUtils::strByPOST("app_id");
$data['cp_order_id'] = paramUtils::strByPOST("cp_order_id");
$data['mem_id'] = paramUtils::strByPOST("mem_id");
$data['order_id'] = paramUtils::strByPOST("order_id");
$data['order_status'] = paramUtils::strByPOST("order_status");
$data['pay_time'] = paramUtils::strByPOST("pay_time");
$data['product_id'] = paramUtils::strByPOST("product_id");
$data['product_name'] = paramUtils::strByPOST("product_name");
$data['product_price'] = paramUtils::strByPOST("product_price");
$data['sign'] = paramUtils::strByPOST("sign");
$data['ext'] = paramUtils::strByPOST("ext");

$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'qidian_super_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("缺少必要参数。");
}
$key = $ch_app_info['app_key'];

$sign = 'app_id='.$data['app_id'].'&cp_order_id='.$data['cp_order_id'].'&mem_id='.$data['mem_id'].'&order_id='.$data['order_id'].'&order_status='.$data['order_status'];
$sign.= '&pay_time='.$data['pay_time'].'&product_id='.$data['product_id'].'&product_name='.strtoupper(urlencode($data['product_name'])).'&product_price='.$data['product_price'].'&app_key='.$key;
if($data['sign'] == md5($sign)){
    $order_info = $DAO->get_super_order($data['cp_order_id']);
    if(!$order_info){
        die('FAILURE');
    }
    if($order_info['status'] != '2' && $data['order_status'] == '2'){
        $DAO->update_super_order_info($data['cp_order_id'],time(),$data['order_id']);
        die("SUCCESS");
    }
    die("SUCCESS");
}else{
    die('FAILURE');
}
