<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('weekactivity_mobile');
$bo = new weekactivity_mobile();
$act = paramUtils::strByGET("act");
switch ($act) {
    case 'index':
        $bo->activity_view();
        break;
    default:
        $bo->activity_view();
        break;
}