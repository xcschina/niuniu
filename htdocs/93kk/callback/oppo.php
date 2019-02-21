<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","ON");
//error_reporting(E_ALL);
COMMON('paramUtils');
BO("game_admin");
$bo = new game_admin();
$params = $_POST;
$bo->err_log(var_export($_POST,1),'oppo_callback_log');
$bo->oppo_callback($params);