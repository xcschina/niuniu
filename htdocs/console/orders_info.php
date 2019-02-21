<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'off');
COMMON("paramUtils","loginCheck");

BO('orders_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new orders_info_web();
switch ($act){
    case"orders_list":
        $bo->get_orders_list();
        break;
    case'order_edit_view':
        $bo->order_edit_view($id);
        break;
    case'order_gift_code_view':
        $bo->order_gift_code_view($id);
        break;
    case'finish_order_view':
        $bo->finish_order_view($id);
        break;
    case'do_edit':
        $bo->order_do_edit();
        break;
    case'do_gift_code_save':
        $bo->do_gift_code_save();
        break;
    case'do_order_finish':
        $bo->do_order_finish();
        break;
    case"order_detail":
        $bo->get_order_detail($id);
        break;
    case"artificial_orders";
        $bo->get_artificial_orders();
        break;
    case"order_examine_view";
        $bo->get_artificia_orders_info($id);
        break;
    case"artificial_orders_examine";
        $bo->do_artificia_orders_examine();
        break;
    case'get_order_imgs';
        $bo->get_order_imgs($id);
        break;
    case'get_sell_order_imgs';
        $bo->get_sell_order_imgs($id);
        break;
    case'order_imgs_add_view';
        $bo->order_imgs_add_view();
        break;
    case'do_order_imgs_add';
        $bo->do_order_imgs_add();
        break;
    case'del_order_img':
        $bo->del_order_img($id);
        break;
    case'order_refund_view':
        $bo->order_refund_view($id);
        break;
    case'do_order_refund':
        $bo->do_order_refund();
        break;
    case'detail_edit':
        $bo->detail_edit();
        break;
    case'export':
        $bo->export();
        break;

    case'audit_order_view':
        $bo->audit_order_view($id);
        break;
    case'do_audit_order_view':
        $bo->do_audit_order_view();
        break;
    case"refuse":
        $bo->refuse($id);
        break;
    case'do_refuse':
        $bo->do_refuse();
        break;
    case"orders_audit":
        $bo->orders_audit($id);
        break;
    case'do_orders_audit':
        $bo->do_orders_audit();
        break;
    case"seller_delivery_detail":
        $bo->seller_delivery_detail($id);
        break;
    default:
        $bo->get_orders_list();
        break;
}