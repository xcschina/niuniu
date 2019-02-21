<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('moyu_index_mobile');

$bo = new moyu_index_mobile();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'index':
        $bo->index_view();
        break;
    default:
        $bo->index_view();
        break;

}