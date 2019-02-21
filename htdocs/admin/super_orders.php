<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('super_orders');

$act = paramUtils::strByGET("act", false);
$app_id = paramUtils::intByGET("app_id");

$bo = new super_orders();
switch ($act){
    case"list":
        $bo->order_list_view( );
        break;
    case'export':
        $bo->export();
        break;
    case'error_list':
        $bo->error_list();
        break;
    case'edit':
        $id = paramUtils::intByGET('id',false);
        $bo->edit_view($id);
        break;
    case'edit_save':
        $bo->edit_save();
        break;
    default:
        $bo->order_list_view();
        break;
}