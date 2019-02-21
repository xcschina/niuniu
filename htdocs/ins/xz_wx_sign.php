<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');

$params = array();

$v = paramUtils::strByGET("v");
$client_timestamp = paramUtils::strByGET("timestamp",false);
$app_id = paramUtils::strByGET("app_id",false);
$api = new android_pay();

$aes = $api->check_user_agent(paramUtils::strByPOST('xzpresign'), $app_id);
foreach($aes as $k=>$param){
    $param = explode("=",$param);
    switch($param[0]){
        case "appId":
            $params['appId'] = $param[1];
            break;
        case "consumerId":
            $params['consumerId'] = $param[1];
            break;
        case "consumerName":
            $params['consumerName'] = $param[1];
            break;
        case "mhtCharset":
            $params['mhtCharset'] = $param[1];
            break;
        case "mhtCurrencyType":
            $params['mhtCurrencyType'] = $param[1];
            break;
        case 'mhtOrderAmt':
            $params['mhtOrderAmt'] = $param[1];
            break;
        case 'mhtOrderDetail':
            $params['mhtOrderDetail'] = $param[1];
            break;
        case "mhtOrderName":
            $params['mhtOrderName'] = $param[1];
            break;
        case "mhtOrderNo":
            $params['mhtOrderNo'] = $param[1];
            break;
        case 'mhtOrderStartTime':
            $params['mhtOrderStartTime'] = $param[1];
            break;
        case 'mhtOrderTimeOut':
            $params['mhtOrderTimeOut'] = $param[1];
            break;
        case 'mhtOrderType':
            $params['mhtOrderType'] = $param[1];
            break;
        case 'notifyUrl':
            $params['notifyUrl'] = $param[1];
            break;
        case 'payChannelType':
            $params['payChannelType'] = $param[1];
            break;
    }
}
ksort($params);
$str = '';
foreach($params as $key => $val ){
    if(!empty($val)){
        $str.=$key."=".$val."&";
    }
}
if($params['appId'] == XZ_HN_SDK_APPID){
    $data = $str.md5(XZ_HN_SDK_KEY);
}else if($params['appId'] == XZ_HNYQ_SDK_APPID){
    $data = $str.md5(XZ_HNYQ_SDK_KEY);
}else{
    $data = $str.md5(XZ_SDK_KEY);
}

$result = array(
    'result'=>1,
    'data'=>"mhtSignature=".md5($data)."&mhtSignType=MD5",
    'desc'=>"success"
);
die("0".base64_encode(json_encode($result)));
