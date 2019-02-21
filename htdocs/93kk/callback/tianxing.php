<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","ON");
//error_reporting(E_ALL);
COMMON('paramUtils');
BO("game_admin");
$bo = new game_admin();
$data = $_GET;
$bo->err_log(var_export($_GET,1),'xigu_callback_log');
$bo->tianxing_callback($data);