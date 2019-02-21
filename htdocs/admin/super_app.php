<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('super_app');

$act = paramUtils::strByGET("act", false);

$bo = new super_app();
switch ($act){
    case "list":
        $bo->app_list_view();
        break;
    case "add":
        $bo->app_add_view();
        break;
    case "do_add":
        $bo->do_app_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->app_edit_view($id);
        break;
    case "do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_app_edit($id);
        break;
    default:
        $bo->app_list_view();
        break;
}