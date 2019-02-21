<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
try{
    $game_id = paramUtils::intByGET("game_id");
    $channel = paramUtils::strByGET("channel");
    $token = paramUtils::strByGET("token");
    $user_id = paramUtils::strByGET("u_id");
    $bo = new game_pay_web();
    $bo->game_login($game_id,$channel,$token,$user_id);
}catch (Exception $e){
    $bo->err_log(var_export($e,1),'h5_game_error');
    print $e;
}
