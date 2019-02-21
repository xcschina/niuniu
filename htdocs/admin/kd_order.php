<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('kd_order_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new kd_order_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"add":
        $bo->add_view();
        break;
    case'do_add':
        $bo->do_add();
        break;
    case"edit":
        $bo->edit($id);
        break;
    case'do_edit':
        $bo->do_edit();
        break;
    case"back_edit":
        $bo->back_edit($id);
        break;
    case'do_back_edit':
        $bo->do_back_edit();
        break;
    case"commit":
        $bo->commit($id);
        break;
    case'do_commit':
        $bo->do_commit();
        break;
    case"qb_channel_commit":
        $bo->qb_channel_commit($id);
        break;
    case'do_qb_channel_commit':
        $bo->do_qb_channel_commit();
        break;
    case"back":
        $bo->back($id);
        break;
    case'do_back':
        $bo->do_back();
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