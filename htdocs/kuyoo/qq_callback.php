<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('account_web');

$bo = new account_web();
$bo->qq_do_login();