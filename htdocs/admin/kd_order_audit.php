<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('kd_order_audit_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new kd_order_audit_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"refuse":
        $bo->refuse($id);
        break;
    case'do_refuse':
        $bo->do_refuse();
        break;
    case"first_audit":
        $bo->first_audit($id);
        break;
    case'do_first_audit':
        $bo->do_first_audit();
        break;
    case"out_money":
        $bo->out_money($id);
        break;
    case'do_out_money':
        $bo->do_out_money();
        break;
    case"get_service":
        $bo->get_service();
        break;
    case"order_export":
        $bo->order_export();
        break;
    default:
        $bo->list_view();
        break;
}