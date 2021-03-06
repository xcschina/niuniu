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
        if($param[0] == 'user_id'){
            $user_id = $param[1];
            $bo->set_usr_session("USR_HEADER", array("user_id"=>$user_id));
        }
    }
}
$act = paramUtils::strByGET("act");
switch ($act) {
    case 'more':
        $bo->pay_more();
        break;
    default:
        $bo->pay_list($user_id);
        break;
}
