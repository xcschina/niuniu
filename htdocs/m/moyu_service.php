<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
BO("moyu_site_mobile");
COMMON('paramUtils');
$bo = new moyu_site_mobile();
$bo->service_view();