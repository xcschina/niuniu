<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('clickServ');
$bo = new clickServ();
$bo->callback_v2();
