<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
require_once COMMON.'alipay_sdk/alipay.config.php';
COMMON('baseCore','paramUtils', 'alipay_sdk/alipay_notify.class');

$bo = new baseCore();
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'alisdk_notify');
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
if($verify_result){
    $order_id = paramUtils::strByPOST("out_trade_no", false);//订单号
    $trade_no = paramUtils::strByPOST("trade_no", false);//支付宝订单号
    $trade_status = paramUtils::strByPOST("trade_status", false);//交易状态  TRADE_SUCCESS 交易成功 TRADE_FINISHED 交易成功且结束
    $buyer_email = paramUtils::strByPOST("buyer_email", false);//买家邮箱

    BO('android_pay');
    $bo = new android_pay();
    $bo->sdk_ali_notify($trade_status, $order_id, $trade_no, $buyer_email,1);
}else{
    $bo->err_log($verify_result,'alisdk_notify');
    echo "fail";
}
?>