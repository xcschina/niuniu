<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");

BO('menu_admin');

$act = paramUtils::strByGET("act", false);

$bo = new menu_admin();
switch ($act){
    case"list":
        $bo->menu_list_view();
        break;
    case"add":
        $pid = paramUtils::intByGET("id");
        $bo->menu_add_view($pid);
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->menu_edit_view($id);
        break;
    case "do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_menu_edit($id);
        break;
    case "do_add":
        $bo->do_menu_add();
        break;
    default:
        $bo->menu_list_view();
        break;
}