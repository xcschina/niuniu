<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_web');

$bo = new index_web();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'get_coupon':
        $bo->get_coupon();
        break;
    default:
        break;
}