<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$bo->err_log($bo->client_ip()."\r".var_export($_GET,1),'xiaomi_super_callback');
$DAO = new callback_dao();
$params = $_GET;
$result = array('errcode'=>'1506');
if($params['orderStatus'] == 'TRADE_SUCCESS'){
    $sp_order_id = $params['cpOrderId'];
    $ch_order_id = $params['orderId'];
    $super_order = $DAO->get_super_order($sp_order_id);
    if(empty($ch_order_id) || empty($super_order)) {
        die(json_encode($result));
    }
    $ch_info = $DAO->get_ch_by_id($params['id']);
    $sign = set_sha1($params,$result,$ch_info['param1']);
    if($sign != $params['signature']){
        $result['errcode'] = '1525';
        die(json_encode($result));
    }
    if($super_order['status'] != '2') {
        $DAO->update_super_order_info($sp_order_id, time(), $ch_order_id);
    }
    $result['errcode'] = '200';
    die(json_encode($result));
}else{
    die(json_encode($result));
}


function set_sha1($params,$result,$AppSecretKey){
    $signature = $params['signature'];
    unset($params['id']);
    unset($params['signature']);
    ksort($params);
    $data_str = "";
    foreach($params as $key => $param){
        if($param !=''){
            $data_str = $data_str."&".$key."=".$param;
        }
    }
    $data_str = substr($data_str, 1);
    $sign = hash_hmac("sha1", $data_str, $AppSecretKey);
    return $sign;
}