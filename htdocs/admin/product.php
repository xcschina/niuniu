<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","On");

BO('product_admin');

$act = paramUtils::strByGET("act", false);

$bo = new product_admin();
switch ($act) {
    case"list":
        $bo->product_list_view();
        break;
    case"add":
        $bo->product_add_view();
        break;
    case"do_add":
        $bo->product_do_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->product_edit_view($id);
        break;
    case"do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->product_do_edit($id);
        break;
    case'export':
        $bo->export();
        break;
    case 'import':
        $bo->import_view();
        break;
    case 'do_import':
        $bo->import_file();
        break;
    case 'tpl_down':
        $bo->tpl_down();
        break;
    default:
        $bo->product_list_view();
        break;
}