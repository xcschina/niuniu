<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt','Security.class');
DAO('callback_dao');
$bo = new baseCore();
$token = paramUtils::strByPOST("token");
$gameid = paramUtils::strByPOST("gameid");
$result = array('result'=>-1,'desc'=>'网络请求出错');
if(empty($gameid) || empty($token)){
    $result['desc']='缺少必要参数';
    die(json_encode($result));
}
$callback_dao = new callback_dao();
$channel_info = $callback_dao->get_channel_app_by_appid($gameid);
if(empty($channel_info)){
    $result['desc']='参数异常';
    die(json_encode($result));
}
$token_info = explode("[AND]",$token);

$user_name = $token_info[0];
$login_time = $token_info[1];
$sign = $token_info[2];
$sign_str = 'username='.$user_name.'&appkey='.$channel_info['app_key'].'&logintime='.$login_time;
if($sign == md5($sign_str)){
    $result = array('result'=>"1",'desc'=>'验证通过');
    die(json_encode($result));
}
$result['desc']='参数验证失败';
die(json_encode($result));