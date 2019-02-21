<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();

$urldata = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';

$success = "SUCCESS";
$fail = "FAILURE";

// 缺少参数
if (empty($urldata)) {
    exit($fail);
}
$super_id = paramUtils::intByREQUEST("id",false);
$urldata = get_object_vars(json_decode($urldata));
$order_id = isset($urldata['order_id']) ? $urldata['order_id'] : '';
$mem_id = isset($urldata['mem_id']) ? $urldata['mem_id'] : '';
$app_id = isset($urldata['app_id']) ? intval($urldata['app_id']) : 0;
$money = isset($urldata['money']) ? $urldata['money'] : 0.00;
$order_status = isset($urldata['order_status']) ? $urldata['order_status'] : '';
$paytime = isset($urldata['paytime']) ? intval($urldata['paytime']) : 0;
$attach = isset($urldata['attach']) ? $urldata['attach'] : ''; //CP扩展参数
$sign = isset($urldata['sign']) ? $urldata['sign'] : ''; // 签名
$bo->err_log($bo->client_ip()."\r".var_export($GLOBALS["HTTP_RAW_POST_DATA"],1),'tyyou_super_sdk_callback');


if(empty($super_id)){
    die("error");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("缺少必要参数。");
}
$appkey = $ch_app_info['app_key'];
////money 参数为小数点后两位
//$money = number_format($money,2);

//1 校验参数合法性
if (empty($urldata) || empty($order_id) || empty($mem_id) || empty($app_id) || empty($money)
    || empty($order_status) || empty($paytime) || empty($attach) || empty($sign)){
    //CP添加自定义参数合法检测
    exit($fail);

}
$paramstr = "order_id=".$order_id."&mem_id=".$mem_id."&app_id=".$app_id."&money=".$money."&order_status=".$order_status."&paytime=".$paytime."&attach=".$attach."&app_key=".$appkey;
$verrifysign = md5($paramstr);

if (0 == strcasecmp($verrifysign, $sign)){
    $order_info = $DAO->get_super_order($attach);
    if(!$order_info){
        exit($fail);
    }
    $DAO->update_super_order_info($attach,time(),$order_id);
    exit($success);
}

exit($fail);