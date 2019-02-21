<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
try{
    $game_id = paramUtils::intByGET("app_id");
    $user_id = paramUtils::strByGET("uid");
    $sign = paramUtils::strByGET("sign");
    $bo = new game_pay_web();
    $bo->user_verify($game_id,$user_id,$sign);
}catch (Exception $e){
    $bo->err_log(var_export($e,1),'user_verify');
    print $e;
}
