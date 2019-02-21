<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('merchant_admin');
$act = paramUtils::strByGET("act", false);
$bo = new merchant_admin();

switch ($act){
    case "consume_log":
        $bo->consume_log();
        break;
    case "consume_detail":
        $id = paramUtils::intByGET("id", false);
        $bo->consume_detail($id);
        break;
    default :
        $bo->consume_log();
}