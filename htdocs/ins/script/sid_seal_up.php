<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('script_admin');
$bo = new script_admin();
$admin = paramUtils::strByGET('user',false);
$bo->sid_seal_up($admin,'6017','mengyou');