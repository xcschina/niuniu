<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('qb_mobile');
$bo = new qb_mobile();
$act = paramUtils::strByGET("act");
die("程序升级中,敬请期待。");
switch ($act) {
    case'pay':
        $bo->pay_qb();
        break;
    case'buy':
        $bo->buy_qb();
        break;
    default:
        $bo->qb_view();
        break;
}