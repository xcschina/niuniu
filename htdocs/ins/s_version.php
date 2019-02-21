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
        $param = explode("=",$param);
        //游戏版本
        if($param[0] == 'ver'){
            $gamever = $param[1];
        }
    }
}

$bo = new android_pay_web();
$bo->s_version($gamever);