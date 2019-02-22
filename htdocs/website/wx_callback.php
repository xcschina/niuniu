<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

COMMON('paramUtils');
BO("game_index_web");
ini_set("display_errors","On");
error_reporting(E_ALL);
$bo = new game_index_web();
$bo->wx_callback();
