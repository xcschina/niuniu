<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore','paramUtils');
BO("android_pay_web");
$bo = new baseCore();
$bo->err_log($bo->client_ip()."\r".var_export($_GET,1),'qq_member');
$appid = paramUtils::strByGET("pid");//app_id
if(empty($appid)){
    $appid = '1095';
}
$result = array('ret'=>1,'msg'=>'失败');
$sigkey = 'QW1eKRz7pzbA0PMx';
$params = $data = $_GET;
if(!$params['payid']||!$params['dsid']||!$params['drid']||!$params['uid']||!$params['taskid']){
    $result = array('ret'=>'-1','msg'=>'缺少参数');
    die(json_encode($result));
}
unset($params['sign']);
$paramKeySort = array_keys($params);
sort($paramKeySort, SORT_STRING);
$paramStr = '';
foreach ($paramKeySort as $key){
    //去除以_开头的无效参数
    if(stripos($key, '_') === 0){
        continue;
    }
    $paramStr.= $key."=".$params[$key]."&";
}
$paramStr.= "key=".$sigkey;
$new_sign = strtolower(md5($paramStr));
if($data['sign'] == $new_sign){
    $do = new android_pay_web();
    $do->add_order($data,$appid);
}else{
    $result = array('ret'=>'-2','msg'=>'签名错误');
    die(json_encode($result));
}
die(json_encode($result));

?>