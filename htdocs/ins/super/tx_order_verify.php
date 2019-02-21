<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore');
BO('super_api_web');
$bo = new super_api_web();
$bo->auto_verify();

