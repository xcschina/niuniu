<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay_web');

if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=", $param);
        if ($param[0] == 'app_id') {
            $params['pid'] = $param[1];
        }
        if ($param[0] == 'user_id') {
            $params['usr_id'] = $param[1];
        }
        if ($param[0] == 'osver') {
            $params['osver'] = $param[1];
        }
        if ($param[0] == 'ver') {
            $params['ver'] = $param[1];
        }
        if ($param[0] == 'net') {
            $params['net'] = $param[1];
        }
        if ($param[0] == 'mtype') {
            $params['devicetype'] = $param[1];
        }
        if ($param[0] == 'dt') {
            $params['systemnmae'] = $param[1];
        }
        if ($param[0] == 'mode') {
            $params['logintype'] = $param[1];
        }
        $sdkver = ''; //有的版本没有sdk头选项，防止出错
        if ($param[0] == 'sdkver') {
            $params['sdkver'] = $param[1];
        }
        if ($param[0] == 'mac') {
            $params['mac'] = $param[1];
        }
        if ($param[0] == 'adtid') {
            $params['adtid'] = $param[1];
        }
        if ($param[0] == 'nickname') {
            $params['nickname'] = $param[1];
        }
        if ($param[0] == 'md5') {
            $params['md5'] = $param[1];
        }
        if ($param[0] == 'channel') {
            $params['channel'] = $param[1];
        }
        if ($param[0] == 'apple_id') {
            $params['apple_id'] = $param[1];
        }
    }
}
if(!$params){
    $result = array('result'=>0,'desc'=>'缺少必要参数');
    die("0".base64_encode(json_encode($result)));
}
$result = array('result'=>1,'desc'=>'success','host'=>'');
die("0".base64_encode(json_encode($result)));
