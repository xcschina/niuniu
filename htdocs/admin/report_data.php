<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('report_data_admin');
$act = paramUtils::strByGET("act", false);
$bo = new report_data();
switch ($act) {
    case"data_info":
        $bo->data_info_view();
        break;
    default:
        $bo->data_info_view();
        break;
}