<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("ios_pay");

$bo = new ios_pay();

$bo->ios_verify_order();