<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('account_mobile');

$bo = new account_mobile();
$bo->qq_do_login();