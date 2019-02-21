<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");
BO('business_inside_admin');
$act = paramUtils::strByGET("act", false);

$bo = new business_inside_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"add":
        $bo->add_view();
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id",false);
        $bo->edit($id);
        break;
    case"do_edit":
        $bo->do_edit();
        break;
    case"get_service":
        $bo->get_service();
        break;
    case"configure":
        $bo->configure();
        break;
    case"do_configure":
        $bo->do_configure();
        break;
    case"order_export":
        $bo->order_export();
        break;
    case"order_collect":
        $bo->order_collect();
        break;
    case"order_edit":
        $id = paramUtils::intByGET('id',false);
        $bo->order_edit($id);
        break;
    case"do_order_edit":
        $bo->do_order_edit();
        break;
    case"order_collect_export":
        $bo->order_collect_export();
        break;
    case"money_collect":
        $bo->money_collect();
        break;
    case"money_collect_edit":
        $id = paramUtils::intByGET('id',false);
        $bo->money_collect_edit($id);
        break;
    case"do_money_edit":
        $bo->do_money_edit();
        break;
    case"group_order_collect":
        $bo->group_order_collect();
        break;
    case"group_money_collect":
        $bo->group_money_collect();
        break;
    case"verify_view":
        $id = paramUtils::intByGET('id',false);
        $bo->verify_view($id);
        break;
    case"do_verify":
        $bo->do_verify();
        break;
    case"group_order_export":
        $bo->group_order_export();
        break;
    case"money_export":
        $bo->money_export();
        break;
    case"group_money_export":
        $bo->group_money_export();
        break;
    case"orders_import_view":
        $bo->orders_import_view();
        break;
    case"order_import_do":
        $bo->order_import_do();
        break;
    case"tpl_down":
        $bo->tpl_down();
        break;
}