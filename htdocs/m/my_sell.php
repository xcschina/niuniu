<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","On");
BO("account_sell_mobile");
COMMON('paramUtils');
$bo = new account_sell_mobile();
$act = paramUtils::strByGET("act",false);
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    switch ($act) {
        default:
            $bo->sell_view();
            break;
        case "stock":
            $bo->stock_view($act);
            break;
        case "audit":
            $bo->stock_view($act);
            break;
        case "unput":
            $bo->stock_view($act);
            break;
        case "sell":
            $bo->sell_view($act);
            break;
        case "selled":
            $bo->selled_view();
            break;
        case "stock-item":
            $id = paramUtils::intByGET("id", false);
            $bo->stock_item_view($id);
            break;
        case "stock-item-edit":
            $id = paramUtils::intByGET("id", false);
            $bo->stock_item_edit_view($id);
            break;
        case "stock-item-pub":
            $id = paramUtils::intByGET("id", false);
            $bo->stock_item_edit_pub($id);
            break;
        case "stock-item-undo-audit":
            $id = paramUtils::intByGET("id", false);
            $token = paramUtils::strByGET("token", false);
            $bo->stock_item_undo_audit($id, $token);
            break;
        case "stock-item-delete":
            $id = paramUtils::intByGET("id", false);
            $token = paramUtils::strByGET("token", false);
            $bo->stock_item_delete($id, $token);
            break;
        case "selled-item":
            $id = paramUtils::intByGET("id", false);
            $bo->selled_item_info($id);
            break;
    }
}else{
    switch ($act) {
        default:
            $bo->sell_view();
            break;
        case "stock-item-edit-check":
            $id = paramUtils::intByGET("id", false);
            $bo->stock_item_edit_check($id);
            break;
        case "stock-item-edit-pub":
            $id = paramUtils::intByGET("id", false);
            $bo->do_stock_item_edit_pub($id);
            break;
    }
}