<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");
BO('business_inside_second');
$act = paramUtils::strByGET("act", false);
$bo = new business_inside_second();
switch ($act) {
    case"order_collect":
        $bo->order_collect();
        break;
    case"money_collect":
        $bo->money_collect();
        break;
    case"del_order":
        $id = paramUtils::intByGET('id',false);
        $bo->del_order($id);
        break;
    case"do_del":
        $id = paramUtils::intByGET('id',false);
        $bo->do_del($id);
        break;
    case"del_list":
        $bo->del_list();
        break;
    case"del_export":
        $bo->del_export();
        break;
    case"money_detail":
        $bo->money_detail();
        break;
    case"detail_export":
        $bo->detail_export();
        break;
    case"business_order_export":
        $bo->business_order_export();
        break;
    case"business_money_export":
        $bo->business_money_export();
        break;
    case"game_sell":
        $bo->game_sell();
        break;
    case"add_sell":
        $bo->add_sell();
        break;
    case"do_add_sell":
        $bo->do_add_sell();
        break;
    case"edit_sell":
        $id = paramUtils::intByGET('id',false);
        $bo->edit_sell($id);
        break;
    case"do_edit_sell":
        $bo->do_edit_sell();
        break;
    case"orders_import_view":
        $bo->orders_import_view();
        break;
    case"import_do":
        $bo->import_do();
        break;
    case"sell_export":
        $bo->sell_export();
        break;
    case"tpl_down":
        $bo->tpl_down();
        break;
    case"repair_log":
        $bo->repair_log();
        break;
    case"add_repair":
        $bo->add_repair();
        break;
    case"do_add_repair":
        $bo->do_add_repair();
        break;
}