<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('qb_channel_refund_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new qb_channel_refund_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"order_export":
        $bo->order_export();
        break;
    case"upload":
        $bo->upload($id);
        break;
    case"do_upload":
        $bo->do_upload();
        break;
    case"refuse":
        $bo->refuse($id);
        break;
    case"do_refuse":
        $bo->do_refuse();
        break;
    default:
        $bo->list_view();
        break;
}