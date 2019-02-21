<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('service_admin');
$act = paramUtils::strByGET("act", false);
$bo = new service_admin();
switch ($act){
    case"service_list":
        $bo->get_service_list();
        break;
    case"service_add_view":
        $bo->service_add_view();
        break;
    case"do_service_add":
        $bo->add_service();
        break;
    case"service_edit_view":
        $id = paramUtils::intByGET("id");
        $bo->service_edit_view($id);
        break;
    case"do_service_edit":
        $bo->do_service_edit();
        break;
    case "service_import_view":
        $bo->service_import_view();
        break;
    case "service_import_do":
        $bo->service_import_do();
        break;
    case"tpl_down":
        $bo->tpl_down();
        break;
    case"import_do":
        $bo->import_do();
        break;
    default:
        $bo->get_service_list();
        break;

}