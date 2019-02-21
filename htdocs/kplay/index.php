<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("index_admin");
COMMON('paramUtils');
$bo = new index_admin();
$bo->index_view();