<?php

header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
BO("service_center");
$bo = new service_center();
//$bo->view();