<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
//ini_set("display_errors","On");
//error_reporting(E_ALL);

BO('orders_admin');

$act = paramUtils::strByGET("act", false);
$app_id = paramUtils::intByGET("app_id");

$bo = new orders_admin();
switch ($act){
    case"list":
        $bo->order_list_view($app_id);
        break;
    case"ch_list":
        $bo->ch_order_list();
        break;
    case"niu_coin_list":
        $bo->niu_coin_list();
        break;
    case"nnb_order":
        $bo->nnb_order();
        break;
    case'export':
        $bo->export();
        break;
    case'ch_export':
        $bo->ch_export();
        break;
    case"error_list":
        $bo->error_list();
        break;
    case"edit":
        $id = paramUtils::intByGET("id",false);
        $bo->edit_view($id);
        break;
    case"edit_save":
        $bo->edit_save();
        break;
    case"apple_list":
        $bo->apple_list();
        break;
    case"apple_export":
        $bo->apple_export();
        break;
    case"app_info":
        $bo->app_info();
        break;
    case"qq_member":
        $bo->qq_member();
        break;
    case"apple_fail_list":
        $bo->apple_fail_list();
        break;
    case"apple_orders_update":
        $bo->apple_orders_update();
        break;
    case"do_apple_orders_update":
        $bo->do_apple_orders_update();
        break;
    case"apple_orders_reg":
        $bo->apple_orders_reg();
        break;
}