<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
$bo = new game_pay_web();
$pid = paramUtils::intByGET("game_id");
$ch = paramUtils::intByGET("ch");
if(empty($pid)){
    die("缺少必要参数");
}
$bo->games_view($pid,$ch);