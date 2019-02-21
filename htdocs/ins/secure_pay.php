<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','jsonUtils');
BO('android_secure_pay_api_web');

$params = array('usr_id'=>0,
                'nickname'=>'',
                'mac'=>'',
                'app_id'=>0,
                'player_id'=>0,
                'playe_rname'=>'',
                'serv_id'=>0,
                'serv_name'=>'',
                'token'=>''
);

$v = paramUtils::strByGET("v",false);
$client_timestamp = paramUtils::strByGET("timestamp",false);
$UA  = paramUtils::strByPOST('ua',false);
if($v!=="3"){
    exit;
}
//$UA = '0cGlkPTEwMDAmYXBwb3JkZXJjb2RlPW51bGwmZ29vZGNvZGU9bnVsbCZwbGF5ZXJpZD1yb2xlSWQwMSZwbGF5ZXJuYW1lPXJvbGVOYW1lMDEmc2VydmlkPXNlcnZJZDAxJnNlcnZuYW1lPXNlcnZOYW1lMDEmbG9naW49Nzk1Njg2Jm5pY2tuYW1lPXlkNTA1NCZtYWM9ODg6MzI6OUI6NTU6M0M6OUImdXNlcnRva2VuPTM1NTUzMzA1NDMxNDk4NSZtZDU9ZjRlMjk1YmM5YTk1NDhhODVkOTkwMWQ4ODk2ZGM3NWY=';

$UA = base64_decode(substr($UA,1));
$UA = explode("&",$UA);
foreach($UA as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]){
        case "userid":
            $params['usr_id'] = $param[1];
            break;
        case "pid":
            $params['app_id'] = $param[1];
            break;
        case "playerid":
            $params['player_id'] = $param[1];
            break;
        case "playername":
            $params['player_name'] = $param[1];
            break;
        case 'servid':
            $params['serv_id'] = $param[1];
            break;
        case 'servname':
            $params['serv_name'] = $param[1];
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
        case 'channel':
            $params['channel'] = $param['1'];
            break;
    }
}

$api = new android_secure_pay_api_web();
if(!$params['usr_id'] || !$params['nickname'] || !$params['app_id']){
    $api->V->assign("msg", "你还没登入账号");
    $api->V->display("error.html");
    exit;
}else{
    $api->set_usr_session("USR_HEADER", $params);
}
try{
    $api->android_pay_view();
}catch (Exception $e){
    $bo->err_log(var_export($e,1),"android-secure-pay");
}