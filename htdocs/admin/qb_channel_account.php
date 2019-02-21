<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('qb_channel_account_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new qb_channel_account_admin();
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
    case"delete":
        $bo->delete($id);
        break;
    case'do_delete':
        $bo->do_delete();
        break;
    default:
        $bo->list_view();
        break;
}