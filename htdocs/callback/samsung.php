<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$params = $data = $_POST;
//unset($prarms['sign']);
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'sumsung_super_callback');

if(empty($super_id)){
    die("FAIL");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("FAILURE");
}
$transdata = htmlspecialchars_decode($params['transdata']);
$transdata = json_decode($transdata,true);

if($transdata['transtype'] === 0){
    $order_info = $DAO->get_super_order($transdata['cporderid']);
    if(!$order_info){
        die('FAILURE');
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($transdata['cporderid'],time(),$transdata['transid']);
    }
    die("SUCCESS");
}else{
    die("FAILURE");
}