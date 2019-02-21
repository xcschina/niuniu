<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt','Security.class');
DAO('callback_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'yxf_sdk_callback');
$super_id = paramUtils::intByREQUEST("id",false);
//$params = $_POST;
//$orderid = $params['orderid'];
//$username = $params['username'];
//$gameid = $params['gameid'];
//$roleid = $params['roleid'];
//$serverid = $params['serverid'];
//$paytype = $params['paytype'];
//$amount = $params['amount'];
//$paytime = $params['paytime'];
//$attach = $params['attach'];
//$sign = $params['sign'];
$super_id = paramUtils::intByREQUEST("id",false);
$orderid = paramUtils::strByPOST("orderid");
$username = paramUtils::strByPOST("username");
$gameid = paramUtils::intByPOST("gameid");
$roleid = paramUtils::strByPOST("roleid");
$serverid = paramUtils::intByPOST("serverid");
$paytype = paramUtils::strByPOST("paytype");
$amount = paramUtils::intByPOST("amount");
$paytime = paramUtils::intByPOST("paytime");
$attach = paramUtils::strByPOST("attach");
$sign = paramUtils::strByPOST("sign");
if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("error");
}
$new_str = 'orderid='.$orderid.'&username='.$username.'&gameid='.$gameid.'&roleid='.$roleid.'&serverid='.$serverid;
$new_str.= '&paytype='.$paytype.'&amount='.$amount.'&paytime='.$paytime.'&attach='.$attach.'&appkey='.$ch_app_info['app_key'];
$new_sign = md5(urldecode($new_str));
if($new_sign == $sign){
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
    die("success");
}else{
    die("errorSign");
}

