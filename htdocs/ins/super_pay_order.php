<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');

$app_id = paramUtils::strByGET("app_id",false);
$api = new android_pay();
$UA = $api->super_check_user_agent(paramUtils::strByPOST('ua'), $app_id);
$ch = '66173';
foreach($UA as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]) {
        case "gamename":
            $params['game_name'] = $param[1];
            break;
        case "payamount":
            $params['pay_amount'] = $param[1];
            break;
        case "currencyname":
            $params['currency_name'] = $param[1];
            break;
        case "goodid":
            $params['money_id'] = $param[1];
            break;
        case "app_id":
            $params['app_id'] = $param[1];
            break;
        case "orderdesc":
            $params['order_desc'] = $param[1];
            break;
        case 'cporderid':
            $params['cp_order_id'] = $param[1];
            break;
        case 'payexpanddata':
            $params['payexpanddata'] = $param[1];
            break;
        case 'safety':
            $params['safety'] = $param[1];
            break;
        case 'sdkver':
            $params['sdkver'] = $param[1];
            break;
        case 'sid':
            $params['sid'] = $param[1];
            break;
        case 'sid2':
            $params['sid2'] = $param[1];
            break;
        case "user_id":
            $params['usr_id'] = $param[1];
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
//            $params['usertoken'] = urldecode($param[1]);
            break;
        case 'user_id':
            $params['user_id'] = $param[1];
            break;
        case 'md5':
            $params['md5'] = $param[1];
            break;
        case 'channel':
            $params['channel'] = $param[1];
            break;
        case 'goodmultiple'://倍数/购买次数
            $params['goodmultiple'] = $param[1];
            break;
        case 'sdkparam1':
            $params['sdkparam1'] = $param[1];
            break;
        case 'sdkparam2':
            $params['sdkparam2'] = $param[1];
            break;
        case 'md52':
            $params['md52'] = $param[1];
            break;
    }
}

if(!$params['usr_id'] || !$params['app_id'] || !$params['player_id'] || !$params['serv_id'] || !$params['money_id']){
    $api->err_log(var_export($params,1),"super_pay_params");
    $result = array("errcode" => 1, "msg" => "缺少必要参数");
    die("0".base64_encode(json_encode($result)));
}else{
    $api->err_log(var_export($params,1),"super_pay_params_success");
    $api->set_usr_session("USR_HEADER", $params);
}

try{
    $api->create_pay_order();
}catch (Exception $e){
    $api->err_log(var_export($params,1),"android-secure-pay");
    $api->err_log(var_export($e,1),"android-secure-pay");
}

function set_ua($ua){
    $ua = str_replace(" ","+",$ua);
    $ua = base64_decode(substr($ua,1));
    $ua = explode("&",$ua);
    return $ua;
}