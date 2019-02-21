<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("games_wx");
$bo = new games_wx();
$pid = paramUtils::intByGET("game_id");
$ch = paramUtils::intByGET("ch");
if(empty($pid)){
    die("缺少必要参数");
}
$bo->bj_games_view($pid,$ch);
