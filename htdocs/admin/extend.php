<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");
BO('extend_admin');

$act = paramUtils::strByGET("act", false);

$bo = new extend_admin();
switch ($act){
    case "list":
        $bo->extend_list_view();
        break;
    case "extend_add":
        $bo->extend_add();
        break;
    case "add_save":
        $bo->add_save();
        break;
    case "extend_exit":
        $id = paramUtils::intByGET("id",false);
        $bo->extend_exit($id);
        break;
    case "record_list":
        $bo->record_list_view();
        break;
    case "ad_view":
        $id = paramUtils::intByGET("id",false);
        $bo->ad_view($id);
        break;
    case "exit_save":
        $bo->exit_save();
        break;
    case "device_search":
        $bo->device_search();
        break;
    case "del":
        $id = paramUtils::intByGET("id",false);
        $bo->device_del($id);
        break;
    case "do_del":
        $bo->device_do_del();
        break;
}