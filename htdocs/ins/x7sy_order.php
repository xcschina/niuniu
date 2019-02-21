<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');
DAO('android_pay_dao');
$api = new android_pay();
$api->err_log(var_export($_POST,1),'x7sy_order');
$result = array('result' => 0, 'desc' => '网络请求出错。');
if($_POST['sign']==md5($_POST['game_guid'].$_POST['game_role_id'].$_POST['game_orderid'].$_POST['timestamp'])){
    if(empty($_POST['appid'])||empty($_POST['channel'])){
        $result['desc']= '缺少必要参数';
        die("0".base64_encode(json_encode($result)));
    }
    $callback_dao = new android_pay_dao();
    $channel_info = $callback_dao->get_ch_by_appid($_POST['appid'],$_POST['channel']);
    if(empty($channel_info)){
        $result['desc']= '参数异常';
        die("0".base64_encode(json_encode($result)));
    }
    $sign = 'extends_info_data='.$_POST['extends_info_data'].'&game_area='.$_POST['game_area'].'&game_guid='.$_POST['game_guid'].'&game_level='.$_POST['game_level'].'&game_orderid='.$_POST['game_orderid'];
    $sign.= '&game_price='.$_POST['game_price'].'&game_role_id='.$_POST['game_role_id'].'&game_role_name='.$_POST['game_role_name'].'&notify_id='.$_POST['notify_id'];
    $sign.= '&subject='.$_POST['subject'].$channel_info['param2'];
    $new_sign = md5($sign);
    $result['result']= 1;
    $result['desc']= '请求成功';
    $result['sign']= $new_sign;
}else{
    $result['desc']= 'sign 无效';
    $result['sign']= $_POST['sign'];
    $result['sign_str']= $_POST['game_guid'].$_POST['game_role_id'].$_POST['game_orderid'].$_POST['timestamp'];
    $result['new_sign']= md5($_POST['game_guid'].$_POST['game_role_id'].$_POST['game_orderid'].$_POST['timestamp']);
}
die("0".base64_encode(json_encode($result)));