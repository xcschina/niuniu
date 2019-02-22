<?php
ini_set("display_errors","On");
error_reporting(E_ALL);
require_once "config.php";
BO("device_controller");
$bo = new device_controller();
$bo->do_an_device_up();