<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("mobile_service_admin");
COMMON('paramUtils');
$bo = new mobile_service_admin();
$bo->list_view();