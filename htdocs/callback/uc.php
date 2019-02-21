<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$input = file_get_contents("php://input");//$GLOBALS['HTTP_RAW_POST_DATA'];
$bo->err_log($bo->client_ip()."\r".var_export($input,1),'uc_sdk_callback');
if(empty($super_id)){
    die("FAILURE");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(empty($ch_app_info)){
    die("FAILURE");
}
$data = json_decode($input, true);
$notify_data = $data['data'];
ksort($notify_data);
$str = '';
foreach($notify_data as $key => $val ){
        $str.= $key."=".$val;
}
$str = $str.$ch_app_info['app_key'];
$sign = strtolower(md5($str));
if($sign == $data['sign']){
    $order_info = $DAO->get_super_order($notify_data['cpOrderId']);
    if(empty($order_info['product_id']) || empty($order_info)){
        die("FAILURE");
    }
    $super_info = $DAO->get_super_info($order_info['app_id']);
    if(!$super_info['app_key']){
        die("FAILURE");
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($notify_data['cpOrderId'],time(),$notify_data['orderId']);
    }
    die("SUCCESS");
}else{
    die("FAILURE");
}