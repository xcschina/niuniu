<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$bo->err_log($bo->client_ip().'POST'."\r".var_export($_POST,1),'oppo_sdk_callback');

$DAO = new callback_dao();
function rsa_verify($contents) {
    $str_contents = "notifyId={$contents['notifyId']}&partnerOrder={$contents['partnerOrder']}&productName={$contents['productName']}&productDesc={$contents['productDesc']}&price={$contents['price']}&count={$contents['count']}&attach={$contents['attach']}";
    $publickey= $ch_info['app_key'];
    $publickey = file_get_contents(PREFIX.'/common/super/keys/oppo_pay_rsa_public_key.pem');
    $public_key_id = openssl_pkey_get_public($publickey);
    $signature =base64_decode($contents['sign']);
    return openssl_verify($str_contents, $signature, $public_key_id );//成功返回1,0失败，-1错误,其他看手册
}
$data = $_POST;
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(empty($ch_app_info)){
    die('result=FAIL&resultMsg=缺少必要参数');
}
$result = rsa_verify($data);

if($result){
    $DAO = new callback_dao();
    $sp_order_id = $data['partnerOrder'];
    $ch_order_id = $data['notifyId'];
    $super_order = $DAO->get_super_order($sp_order_id);
    if(empty($ch_order_id) || empty($super_order)) {
        die('result=FAIL&resultMsg=订单查询失败');
    }
    if($super_order['status'] != '2') {
        $DAO->update_super_order_info($sp_order_id, time(), $ch_order_id);
    }
    die('result=OK&resultMsg=成功');}
else{
    die('result=FAIL&resultMsg=加密失败');
}