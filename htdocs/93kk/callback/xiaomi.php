<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","ON");
//error_reporting(E_ALL);
COMMON('paramUtils');
BO("game_admin");
$bo = new game_admin();
$input = file_get_contents("php://input");
parse_str($input, $data);
$params = json_decode(array_keys($data)[0],true);
$bo->err_log(var_export($params,1),'xiaomi_h5_callback_log');
$bo->err_log(var_export($data,1),'xiaomi_h5_get_log');
$bo->xiaomi_callback($params);