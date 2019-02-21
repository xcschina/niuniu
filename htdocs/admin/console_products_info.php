<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'off');
COMMON("paramUtils","loginCheck");

BO('console_products_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new console_products_info_web();
    switch ($act){
        case"list":
            $bo->get_products_list();
            break;
        case'export':
            $bo->export();
            break;
        case"refuse":
            $bo->refuse($id);
            break;
        case'do_refuse':
            $bo->do_refuse();
            break;
        case"products_audit":
            $bo->products_audit($id);
            break;
        case'do_products_audit':
            $bo->do_products_audit();
            break;
        default:
            $bo->get_products_list();
            break;
}