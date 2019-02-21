<?php
//ini_set("display_errors","off");
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('index_admin');
$bo = new index_admin();
$bo->index_view();
