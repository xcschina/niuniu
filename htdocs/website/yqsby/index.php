<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

COMMON('paramUtils');
BO("game_index_web");
$bo = new game_index_web();
$bo->pay_view();