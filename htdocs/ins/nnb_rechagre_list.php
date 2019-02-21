<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("android_pay_web");
ini_set("display_errors","on");
$bo = new android_pay_web();
if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);
        switch($param[0]) {
            case "user_id":
                $params['user_id'] = $user_id = $param[1];
                break;
            case "app_id":
                $params['app_id'] = $param[1];
                break;
        }
    }
    $bo->set_usr_session("USR_HEADER", $params);
}
$act = paramUtils::strByGET("act");
switch ($act) {
    case 'more':
        $bo->nnb_recharge_more();
        break;
    default:
        $bo->nnb_recharge_list($user_id);
        break;
}
