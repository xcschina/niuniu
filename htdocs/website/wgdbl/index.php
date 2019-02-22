<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

COMMON('paramUtils');
BO("game_index_web");
$bo = new game_index_web();
$act = paramUtils::strByGET("act");
switch($act){
    default:
        $bo->new_pay_view();
        break;
    case 'zzk':
        $goodid = 719;
        $bo->new_pay_by_goodid($goodid);
        break;
}
