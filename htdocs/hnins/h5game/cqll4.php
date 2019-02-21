<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$game_ver = paramUtils::strByGET('ver');
$bo = new feedback();
$bo->V->display("h5game/cqll4_game.html");
