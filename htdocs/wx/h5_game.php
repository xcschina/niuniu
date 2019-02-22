<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("h5_games_wx");
$bo = new h5_games_wx();
$bo->game_login();
