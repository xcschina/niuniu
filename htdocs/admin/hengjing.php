<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
//error_reporting(E_ALL);
//ini_set("display_errors","on");

BO('hengjing_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new hengjing_admin();
    switch ($act){
        case"list":
            $bo->hengjing_list_view();
            break;
        case"add":
            $bo->hengjing_add_view();
            break;
        case"do_add":
            $bo->do_add();
            break;
        case "export":
            $bo->export();
            break;
        case"province_list":
            $bo->province_list();
            break;
        case"add_province":
            $bo->add_province();
            break;
        case"do_add_province":
            $bo->do_add_province();
            break;
        case'province_edit':
            $id = paramUtils::intByGET('id',false);
            $bo->province_edit($id);
            break;
        case'do_edit_province':
            $bo->do_edit_province();
            break;
        default:
            sleep(10);
            break;
}