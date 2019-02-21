<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('qb_order_audit_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new qb_order_audit_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"edit":
        $bo->edit($id);
        break;
    case'do_edit':
        $bo->do_edit();
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
    case"second_audit":
        $bo->second_audit($id);
        break;
    case'do_second_audit':
        $bo->do_second_audit();
        break;
    case"get_service":
        $bo->get_service();
        break;
    case"desc_detail":
        $bo->desc_detail($id);
        break;
    case"order_export":
        $bo->order_export();
        break;
    default:
        $bo->list_view();
        break;
}