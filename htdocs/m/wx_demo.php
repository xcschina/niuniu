<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('wx_demo');
$bo = new wx_demo();
$bo->index();