<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_mobile');
$bo = new index_mobile();
$bo->coin_view();