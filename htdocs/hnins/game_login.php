<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
try{
    $id = paramUtils::intByGET("id");
    $sign = paramUtils::intByGET("sign");
    $bo = new game_pay_web();
//    $bo->h5_game_view($id,$sign);
    $bo->nn_h5_game_view($id,$sign);
}catch (Exception $e){
    $bo->err_log(var_export($e,1),'h5_r_game_error');
    print $e;
}
