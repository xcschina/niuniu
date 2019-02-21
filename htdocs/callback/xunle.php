<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$prarms = $data = $_POST;
unset($prarms['sign']);
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'xunle_super_sdk_callback');

if(empty($super_id)){
    die("FAIL");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("FAIL");
}
function rsaVerify($data, $pKey, $sign)  {
    $res = openssl_get_publickey($pKey);
    $result = (bool)openssl_verify($data, base64_decode($sign), $res,OPENSSL_ALGO_SHA1);
    openssl_free_key($res);
    return $result;
}

$pubKey= $ch_app_info['param2'] ;
$pubKey='-----BEGIN PUBLIC KEY-----'.PHP_EOL
    .chunk_split($pubKey, 64, PHP_EOL)
    .'-----END PUBLIC KEY-----'.PHP_EOL;

$data = $_POST["data"];
$data = htmlspecialchars_decode(htmlspecialchars_decode($data));
$sign = $_POST["sign"];
$vret=rsaVerify($data,$pubKey,$sign);
if($vret){
    $data = json_decode($data);
    $order_info = $DAO->get_super_order($data->orderExt);
    if(!$order_info){
        die('FAIL');
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($data->orderExt,time(),$data->orderId);
        die("SUCCESS");
    }
    echo "SUCCESS";
}else{
    die("FAIL");
}
die("FAIL");