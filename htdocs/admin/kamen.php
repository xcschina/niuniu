<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('kamen_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new kamen_admin();
    switch ($act){
        case"list":
            $bo->kamen_list_view();
            break;
        case"add":
            $bo->kamen_add_view();
            break;
        case"do_add":
            $bo->do_add();
            break;
        case "edit":
            $bo->edit_view($id);
            break;
        case"do_edit":
            $bo->do_edit($id);
            break;
        default:
            sleep(10);
            break;
}