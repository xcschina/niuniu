<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$prarms = $post_data = $_POST;
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'ppyou_super_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("缺少必要参数。");
}

$url = 'http://papau.cn:10040/sdkapi/';
$timestamp = time();
$time = date("YmdHis",$timestamp);
$extime = date("YmdHis",($timestamp+60*10));


$api_key = $ch_app_info['app_key'];
$paytype = $_POST['paytype'];
$sign = $_POST['sign'];
$deal_price = $_POST['dealprice'];
$order_no = $_POST['orderno'];
$out_order_no = $_POST['outorderno'];
$submit_time = $_POST['submittime'];
$user_id = $_POST['userid'];

$signSrc = $submit_time.$out_order_no.$order_no.$user_id.$deal_price.$paytype.$api_key;
$signSrc = strtoupper(md5($signSrc));

if($sign == $signSrc) {
    $order_info = $DAO->get_super_order($out_order_no);
    if(!$order_info){
        die('10002');
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($out_order_no,time(),$order_no);
        echo "10001";
        exit();
    }
    echo "10001";
    exit();
}else {
    die('10002');
}

