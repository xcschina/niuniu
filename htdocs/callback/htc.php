<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$input = file_get_contents("php://input");//$GLOBALS['HTTP_RAW_POST_DATA'];
parse_str($input, $data);
$bo->err_log($bo->client_ip()."\r".var_export($data,1),'htc_super_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key'] || !$ch_app_info['param2']){
    die("缺少必要参数。");
}

if(!isset($data['order']) || !isset($data['sign'])) {
    die("fail0 data error");
}
$order = substr(urldecode($data['order']), 1, -1);
$sign = $data['sign'];

$pub_key = setupPubKey($ch_app_info['param2']); //$AppSecret 为pubkey


$dataStatus = rsa_verify($order,$sign,$pub_key);

if(!$dataStatus) {
    die("fail2 data invalid");
}


$orderData=json_decode($order,true);
$result_code = $orderData['result_code']; //1代表支付成功 0代表支付失败
$gmt_create = $orderData['gmt_create']; //支付订单创建时间 格式：yyyy-MM-dd HH:mm:ss;
$amount = $orderData['real_amount']; //用户真实付费金额，单位为分
$result_msg = $orderData['result_msg']; //支付结果提示语，成功=支付成功；失败=支付失败
$game_code = $orderData['game_code']; //游戏编号
$game_order_id = $orderData['game_order_id']; //游戏订单id ,透传
$orderId = $orderData['jolo_order_id']; //jolo支付订单id
$gmt_payment = $orderData['gmt_payment']; //支付时间
if($result_code != 1){
    die("fail3 data pay invalid");
}else{
    $order_info = $DAO->get_super_order($game_order_id);
    if(!$order_info){
        die("no order");
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($game_order_id,time(),$orderId);
        die("success");
    }
    die("success");
}
return "fail4";


function setupPubKey($pubKey){
    if (is_resource($pubKey)){
        return true;
    }
    $pem = chunk_split($pubKey,64,"\n");
    $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
    $pub_Key= openssl_pkey_get_public($pem);
    return $pub_Key;
}

function rsa_verify($data, $signature, $public_key){
    if(empty($data) || empty($signature)){
        return false;
    }
    $pub_res = openssl_get_publickey($public_key);
    return openssl_verify($data, base64_decode($signature), $public_key);
}

