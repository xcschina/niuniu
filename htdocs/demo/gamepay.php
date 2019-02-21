<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
//ini_set("display_errors","On");
try{
    $money_id = paramUtils::intByPOST("money_id",false);
    $serv_id = paramUtils::strByPOST("serv_id",false);
    $player_id = paramUtils::strByPOST("player_id",false);
    $usr_name = paramUtils::strByPOST("usr_name",false);
    $ch = paramUtils::strByPOST("ch",false);
    BO("game_pay_web");
    $bo = new game_pay_web();
    $bo->game_pay();
}catch (Exception $e){
    print $e;
}