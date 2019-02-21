<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
BO("site_mobile");
COMMON('paramUtils');
$bo = new site_mobile();
$bo->service_view();