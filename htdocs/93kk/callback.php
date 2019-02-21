<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","ON");
//error_reporting(E_ALL);
COMMON('paramUtils');
BO("game_admin");
$bo = new game_admin();
$data = $_POST;
$bo->err_log(var_export($data,1),'zhijian_callback_log');
$bo->zhijian_callback($data);