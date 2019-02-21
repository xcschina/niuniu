<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

COMMON('paramUtils');
BO('android_pay');
$v = paramUtils::strByGET("v");
$client_timestamp = paramUtils::strByGET("timestamp",false);
$app_id = paramUtils::strByGET("app_id",false);

$api = new android_pay();

$UA = $api->check_user_agent(paramUtils::strByPOST('order_id'), $app_id);
foreach($UA as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]){
        case 'order_id':
            $order_id = $param[1];
            break;
    }
}
$api->query_orders($order_id);