<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('ry_admin');
$act = paramUtils::strByGET("act", false);

$bo = new ry_admin();
switch ($act){
    case"list":
        $bo->ry_list_view();
        break;
    case"add":
        $bo->add_ry_view();
        break;
    case"do_add":
        $bo->ry_do_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->edit_ry_view($id);
        break;
    case"do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->ry_do_edit($id);
        break;
    case"ext_view":
        $bo->ext_view();
        break;
    case"ext_add":
        $bo->ext_add_view();
        break;
    case"ext_do_add":
        $bo->ext_do_add();
        break;
    case"ext_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->ext_edit_view($id);
        break;
    case"ext_do_edit":
        $bo->ext_do_edit();
        break;
}