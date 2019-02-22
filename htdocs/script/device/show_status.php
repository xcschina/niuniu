<?php
header("Content-Type: text/html; charset=utf-8");
ini_set("display_errors","On");
error_reporting(E_ALL);
require_once "config.php";
BO("device_controller");
$bo = new device_controller();
$bo->show_status();