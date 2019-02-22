<?php

header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
BO("index_admin");
COMMON('paramUtils');
$bo = new index_admin();
$bo->index_view();