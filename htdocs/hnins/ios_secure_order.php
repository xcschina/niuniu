<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('ios_pay');

$params = array();

$client_timestamp = paramUtils::strByGET("timestamp",false);
$app_id = paramUtils::strByGET("app_id",false);

$api = new ios_pay();
$UA = $api->check_user_agent(paramUtils::strByPOST('ua'), $app_id);
foreach($UA as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]){
        case "goodsid":
            $params['money_id'] = $param[1];
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
        case 'user_id':
            $params['user_id'] = $param[1];
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
        case 'cp_order_id':
            $params['cp_order_id'] = $param[1];
            break;
        case 'channel':
            $params['channel'] = $param[1];
            break;
        case 'goodmultiple'://倍数/购买次数
            $params['goodmultiple'] = $param[1];
            break;
        case 'currency_type'://货币类型
            $params['currency_type'] = $param[1];
            break;
        case 'pay_type'://支付类型  例如：支付宝，微信，PayPal
            $params['pay_type'] = $param[1];
            break;
        case 'appleid'://苹果id
            $params['apple_id'] = $param[1];
            break;
    }
}
$api->err_log(var_export($UA,1),'ios_secure_order');
if(!$params['user_id'] || !$params['app_id'] || !$params['player_id'] || !$params['serv_id'] ||  !$params['money_id']){
    $result = array("errcode"=>1,"message"=>"参数异常。");
    die("0".base64_encode(json_encode($result)));
}else{
    $api->set_usr_session("USR_HEADER", $params);
    $api->err_log(var_export($params,1),'ios_secure_order');
}
try{
    $api->ios_secure_order();
}catch (Exception $e){
    $api->err_log(var_export($params,1),"ios_secure_order");
    $api->err_log(var_export($e,1),"ios_secure_order");
}

function set_ua($ua){
    $ua = str_replace(" ","+",$ua);
    $ua = base64_decode(substr($ua,1));
    $ua = explode("&",$ua);
    return $ua;
}