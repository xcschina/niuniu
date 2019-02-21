<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$params = $data = $_POST;
$bo->err_log($bo->client_ip()."\r".var_export($_POST,1),'meizu_super_callback');
$result = array('code'=>'900000','message'=>'验证失败');
if(empty($super_id)){
    $result['message'] = '缺少必要参数。';
    die(json_encode($result));
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    $result['message'] = '渠道信息获取失败。';
    die(json_encode($result));
}
unset($data['sign']);
unset($data['sign_type']);
ksort($data);
$sign_str = '';
foreach($data as $k=>$v){
    if(!is_null($v)){
        $sign_str.="&".$k."=".$v;
    }
}
$sign_str = substr($sign_str, 1);
$sign_str = $sign_str.":".$ch_app_info['param1'];
if(md5($sign_str) != $params['sign']){
    $result['message'] = 'sign加密失败。';
    die(json_encode($result));
}
if($params['trade_status'] == '3'){
    $order_info = $DAO->get_super_order($params['cp_order_id']);
    if(!$order_info){
        $result['message'] = '订单异常。';
        die(json_encode($result));
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($params['cp_order_id'],time(),$params['order_id']);
    }
    $result['code'] = 200;
    $result['message'] = 'success';
}else{
    $result['message'] = '订单类似错误。';
}
die(json_encode($result));