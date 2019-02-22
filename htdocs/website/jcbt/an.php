<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_index_web");

$app_id = paramUtils::strByGET("app_id", false);
$timestamp = paramUtils::strByGET("timestamp", false);
$goods = $_POST['ua'];
$bo = new game_index_web();
$params = array();
$UA = $bo->check_user_agent($goods, $app_id);

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
        case 'cp_order_id':
            $params['cp_order_id'] = $param[1];
            break;
        case 'sid':
            $params['sid'] = $param[1];
            break;
    }
}
//$bo->quick_payment($params,1);
$bo->bj_quick_payment($params,1);