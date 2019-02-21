<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('user_balance');

$bo = new user_balance();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'user_order':
        $bo->user_order();
        break;
    case 'user_recharge':
        $bo->user_recharge();

}