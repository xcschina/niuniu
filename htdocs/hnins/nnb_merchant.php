<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('paramUtils');
BO('android_pay_web');

$orderid = paramUtils::strByGET("orderid");
$result = paramUtils::strByGET("result");
$errmsg = paramUtils::strByGET("errormsg");

$bo = new android_pay_web();
if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);

        //应用id
        if($param[0] == 'app_id'){
            $appid = $param[1];
        }
        //用户id
        if($param[0] == 'user_id'){
            $user_id = $param[1];
        }
        //系统固件版本
        if($param[0] == 'osver'){
            $osver = $param[1];
        }
        //游戏版本
        if($param[0] == 'ver'){
            $gamever = $param[1];
        }

        //游戏网络
        if($param[0] == 'net'){
            $net = $param[1];
        }
        //所用设备
        if($param[0] == 'mtype'){
            $mtype = $param[1];
        }
        //系统名
        if($param[0] == 'dt'){
            $osname = $param[1];
        }
        //登录类型
        if($param[0] == 'mode'){
            $logintype = $param[1];
        }

        if($param[0] == 'sdktype'){
            $sdktype = $param[1];
        }
        //设备标识
        if($param[0] == 'sid'){
            $uuid = $param[1];
        }
        //sdk版本
        $sdkver = '';                   //有的版本没有sdk头选项，防止出错
        if($param[0] == 'sdkver'){
            $sdkver = $param[1];
        }
        if($param[0] == 'ch-lang' && $param[1]){
            $language = $param[1];
        }
    }
}
$bo->nnb_merchant($orderid, $result, $errmsg,$user_id);