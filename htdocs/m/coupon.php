<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_mobile');

$bo = new index_mobile();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'get_coupon':
        $bo->get_coupon();
        break;
    case'coupon_list':
        $bo->coupon_view();
    default:
        break;
}