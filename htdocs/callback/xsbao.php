<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore');
DAO('callback_dao');
$bo = new baseCore();
$super_id = paramUtils::intByREQUEST("id",false);
$prarms = $_GET;
$bo->err_log($bo->client_ip()."\r".var_export($_GET,1),'xsbao_super_sdk_callback');

if(empty($super_id)){
    die("FAIL-缺少必要参数");
}
$DAO = new callback_dao();
$ch_app_info = $DAO->get_channel_app_info($super_id);
if(!$ch_app_info['app_key']){
    die("FAIL-缺少必要参数。");
}

$data = array(
    'appid'=>$prarms['appid'],
    'charid'=>$prarms['charid'],
    'cporderid'=>$prarms['cporderid'],
    'extinfo'=>$prarms['extinfo'],
    'gold'=>$prarms['gold'],
    'money'=>$prarms['money'],
    'orderid'=>$prarms['orderid'],
    'serverid'=>$prarms['serverid'],
    'time'=>$prarms['time'],
    'uid'=>$prarms['uid']
);

foreach($data as $key=>$item){
    if(!empty($item)){
        $arg = $arg.$key.'='.$item.'&';
    }
}
$arg = substr($arg,0,strlen($arg)-1);
$sign = md5($arg.$ch_app_info['param2']);
if($sign == $prarms['sign']){
    $order_info = $DAO->get_super_order($prarms['cporderid']);
    if(empty($order_info['product_id']) || empty($order_info)){
        die("FAIL-订单异常。");
    }
    $super_info = $DAO->get_super_info($order_info['app_id']);
    if(!$super_info['app_key']){
        die("FAIL-游戏信息错误。");
    }
    if($order_info['status'] != '2'){
        $DAO->update_super_order_info($prarms['cporderid'],$prarms['time'],$prarms['orderid']);
    }
    die("SUCCESS");
}else{
    die("FAIL");
}
