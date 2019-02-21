<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('super_product');

$act = paramUtils::strByGET("act", false);
$bo = new super_product();
switch ($act) {
    case"list":
        $bo->product_list_view();
        break;
    case"add":
        $bo->product_add_view();
        break;
    case"do_add":
        $bo->product_do_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->product_edit_view($id);
        break;
    case"do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->product_do_edit($id);
        break;
    case'export':
        $bo->export();
        break;
    case"import":
        $bo->import();
        break;
    case"do_import":
        $bo->do_import();
        break;
    case"channel_list":
        $bo->channel_list();
        break;
    case"add_channel":
        $bo->add_channel();
        break;
    case"do_add_channel":
        $bo->do_add_channel();
        break;
    case"get_goods_list":
        $bo->get_goods_list();
        break;
    case"edit_channel":
        $id = paramUtils::intByGET('id',false);
        $bo->edit_channel($id);
        break;
    case'do_edit_channel':
        $bo->do_edit_channel();
        break;
    case"channel_export":
        $bo->channel_export();
        break;
    default:
        $bo->product_list_view();
        break;
}