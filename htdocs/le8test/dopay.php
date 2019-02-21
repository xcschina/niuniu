<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("le8_pay_web");

$hash = paramUtils::strByGET("hash", false);

$bo = new le8_pay_web();
$bo->do_pay($hash);