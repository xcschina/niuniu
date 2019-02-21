<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("site_index_web");
$bo = new site_index_web();
$bo->index_view();