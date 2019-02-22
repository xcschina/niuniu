<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('product_web');
$bo = new product_web();
$bo->ali_pay_return();