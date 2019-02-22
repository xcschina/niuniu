<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
try{
    $money_id = paramUtils::intByPOST("money_id",false);
    $serv_id = paramUtils::strByPOST("serv_id",false);
    $player_id = paramUtils::strByPOST("player_id",false);
    $usr_name = paramUtils::strByPOST("usr_name",false);
    $ch = paramUtils::strByPOST("ch",false);
    BO("new_game_pay_web");
    $bo = new new_game_pay_web();
    $bo->default_game_pay('93kk');
}catch (Exception $e){
    print $e;
}
