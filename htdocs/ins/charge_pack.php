<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("pack_web");

$bo = new pack_web();

$bo->pack_charge();