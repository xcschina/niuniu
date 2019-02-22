<?php
//小程序用户数据收集
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("games_wx");
$bo = new games_wx();
$bo->wx_program_pay();
