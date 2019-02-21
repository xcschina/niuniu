<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('help_web');

$bo = new help_web();
$act = paramUtils::strByGET("act");
switch ($act) {
    default:
        $bo->link_view();
        break;
}