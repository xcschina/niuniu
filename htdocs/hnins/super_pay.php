<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('paramUtils');
BO('android_pay_web');

$params = array('usr_id'=>0, 'nickname'=>'', 'mac'=>'', 'app_id'=>0);

$pay = new android_pay_web();
$v = paramUtils::strByGET("v",false);
$client_timestamp = paramUtils::strByGET("timestamp",false);
$app_id = paramUtils::strByGET("app_id",false);
$good_id = paramUtils::strByGET("goodid");
if($v!=="3"){
    exit;
}
$UA = $pay->check_user_agent(paramUtils::strByPOST('ua'), $app_id);
$ch = '66173';
foreach($UA as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]){
        case "app_id":
            $params['app_id'] = $param[1];
            break;
        case "role_id":
            $params['player_id'] = $param[1];
            break;
        case "role_name":
            $params['player_name'] = $param[1];
            break;
        case 'area_server_id':
            $params['serv_id'] = $param[1];
            break;
        case 'area_server_name':
            $params['serv_name'] = $param[1];
            break;
        case "user_id":
            $params['usr_id'] = $param[1];
            break;
        case "nickname":
            $params['nickname'] = $param[1];
            break;
        case "mac":
            $params['mac'] = $param[1];
            break;
        case 'usertoken':
            $params['token'] = $param[1];
            break;
        case 'md5':
            $params['md5'] = $param[1];
            break;
        case 'payexpanddata':
            $params['payexpanddata'] = $param[1];
            break;
        case 'sdkver':
            $params['sdkver'] = $param[1];
            break;
        case 'usertype':
            $params['usr_type'] = $param[1];
            break;
        case 'mobile':
            $params['mobile'] = $param[1];
            break;
        case 'channel':
            $params['channel'] = $param[1];
            break;
    }
}
$params['ch'] = $ch;
//网站注册用户没有nickname
if(!$params['usr_id'] || !$params['app_id'] || !$params['player_id'] || !$params['serv_id']){
    $pay->assign("msg", "你还没登入账号1");
    $pay->display("error.html");
    exit;
}else{
    $pay->set_usr_session("USR_HEADER", $params);
}
$pay->super_pay($good_id);

function set_ua($ua){
    $ua = str_replace(" ","+",$ua);
    $ua = base64_decode(substr($ua,1));
    $ua = explode("&",$ua);
    return $ua;
}
