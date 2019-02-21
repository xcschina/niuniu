<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();
$params = $_POST;
$super_id = paramUtils::intByREQUEST("id",false);
$app_id = paramUtils::strByPOST("app_id");
//平台订单号
$transaction_id = paramUtils::strByPOST("transaction_id");
//融合订单号
$out_trade_no = paramUtils::strByPOST("out_trade_no");
$total_fee = paramUtils::strByPOST("total_fee");
$payType = paramUtils::strByPOST("payType");
$sign = paramUtils::strByPOST("sign");
$bo->err_log($bo->client_ip()."\r".var_export($params,1),'wuhanduoyou_sdk_callback');

if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();

$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("error");
}
//获得key值
$key = md5($app_id.'GAME_MAKERS'.$ch_app_info['app_key']);
$buff = "";
ksort($params);
foreach ($params as $k => $v){
    if($k != "sign" && $v != "" && !is_array($v)){
        $buff .= $k . "=" . $v . "&";
    }
}
$buff = trim($buff, "&")."&key=".$key;
//生成签名
$string = hash_hmac("sha256",$buff ,$key);
//转换大写
$new_sign = strtoupper($string);
if($new_sign == $sign){
    $order_info = $DAO->get_super_order($out_trade_no);
    if(empty($order_info['product_id']) || empty($order_info)){
        die("error");
    }
    $super_info = $DAO->get_super_info($order_info['app_id']);
    if(!$super_info['app_key']){
        die("error");
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($out_trade_no,time(),$transaction_id);
    }
    die("success");
}else{
    die("errorSign");
}

