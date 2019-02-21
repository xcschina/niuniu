<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();
//$super_id = paramUtils::intByREQUEST("id",false);
$bo->err_log($bo->client_ip().'POST'."\r".var_export($_POST,1),'huawei_sdk_callback');

$DAO = new callback_dao();
$params = $_POST;
$result = array('result' => 3);
if($params['result']!=0){
    die(json_encode($result));
}else{
    $sp_order_id = $params['requestId'];
    $ch_order_id = $params['orderId'];
    $ch_pay_time = $params['notifyTime'];
    $super_order = $DAO->get_super_order($sp_order_id);
    if(empty($ch_order_id) || empty($super_order)) {
        $result['result'] = 98;
        die(json_encode($result));
    }
    if($super_order['status'] != '2') {
        $DAO->update_super_order_info($sp_order_id, time(), $ch_order_id);
    }
    $result['result'] = 0;
    die(json_encode($result));
}