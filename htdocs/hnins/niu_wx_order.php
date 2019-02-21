<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');

$params = array('usr_id'=>0,
                'nickname'=>'',
                'mac'=>'',
                'app_id'=>0,
                'player_id'=>0,
                'player_name'=>'',
                'serv_id'=>0,
                'serv_name'=>'',
                'token'=>'',
                'money_id'=>0,
                'cp_order_id'=>''
);

$v = paramUtils::strByGET("v");
$client_timestamp = paramUtils::strByGET("timestamp",false);
$app_id = paramUtils::strByGET("app_id",false);

$api = new android_pay();
$UA = $api->check_user_agent(paramUtils::strByPOST('ua'), $app_id);
$ch = '66173';
foreach($UA as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]){
        case "goodsid":
            $params['money_id'] = $param[1];
            break;
        case "user_id":
            $params['usr_id'] = $param[1];
            break;
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
        case "nickname":
            $params['nickname'] = $param[1];
            break;
        case "mac":
            $params['mac'] = $param[1];
            break;
        case 'usertoken':
            $params['usertoken'] = $param[1];
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
        case 'price':
            $params['price'] = $param[1];
            break;
        case 'rate':
            $params['rate'] = $param[1];
            break;
        case 'channel':
            $params['channel'] = $param[1];
            break;
    }
}
$api->err_log(var_export($UA,1),'nnb_order');
$api->open_debug();
if(!$params['usr_id'] || !$params['app_id']){
    $pay->assign("msg", "缺少必要参数,请重新登录66账号.");
    $pay->display("error.html");
    exit;
}else{
    $api->set_usr_session("USR_HEADER", $params);
    $api->err_log(var_export($params,1),'nnb_wx_order');
}
try{
    $api->nnb_wx_order();
}catch (Exception $e){
    $api->err_log(var_export($params,1),"nnb-android-wx-pay");
    $api->err_log(var_export($e,1),"nnb-android-wx-pay");
}

function set_ua($ua){
    $ua = str_replace(" ","+",$ua);
    $ua = base64_decode(substr($ua,1));
    $ua = explode("&",$ua);
    return $ua;
}