<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('qb_channel_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo = new qb_channel_admin();
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
        $bo->do_edit($id);
        break;

    default:
        $bo->list_view();
        break;
}