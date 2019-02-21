<?php
header("HTTP/1.1 404 Not Found");
header("Status: 404 Not Found");
exit;
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
COMMON("paramUtils");
BO('recharge_web');

$bo = new recharge_web();
$bo->index_view();