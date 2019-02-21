<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");
BO('business_admin');
$act = paramUtils::strByGET("act", false);

$bo = new business_admin();
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
        $id = paramUtils::intByGET("id", false);
        $bo->edit_view($id);
        break;
    case "do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_edit($id);
        break;
    case "verify":
        $id = paramUtils::intByGET("id", false);
        $bo->verify_view($id);
        break;
    case "export":
        $bo->export();
        break;
    case "again_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->again_edit_view($id);
        break;
    case "get_service":
        $bo->get_service();
        break;
    case "review":
        $bo->review_view();
        break;
    case "reason_view":
        $id = paramUtils::intByGET("id", false);
        $bo->reason_view($id);
        break;
    case "do_review":
        $id = paramUtils::intByGET("id", false);
        $bo->do_review($id);
        break;
    case "do_again_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_again_edit($id);
        break;
    case "order_log":
        $id = paramUtils::intByGET("id", false);
        $bo->get_order_log($id);
        break;
    case "order_log_list":
        $bo->order_log_list();
        break;
    case "add_charge":
        $bo->add_charge();
        break;
    case "do_add_charge":
        $bo->do_add_charge();
        break;
    case "log_details":
        $id = paramUtils::intByGET("id", false);
        $bo->log_details($id);
        break;
    case "do_edit_charge":
        $bo->do_edit_charge();
        break;
    case "get_account":
        $bo->get_account();
        break;
    case "payment_log":
        $bo->payment_log();
        break;
    case "add_payment":
        $bo->add_payment();
        break;
    case "do_add_payment":
        $bo->do_add_payment();
        break;
    case "import_data":
        $bo->import_view();
        break;
    case "do_import":
        $bo->do_import();
        break;
    case "order_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->order_edit($id);
        break;
    case "do_order_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_order_edit($id);
        break;
    case "del_log":
        $id = paramUtils::intByGET("id",false);
        $bo->del_log($id);
        break;
    case "do_del":
        $id = paramUtils::intByGET("id",false);
        $bo->do_del($id);
        break;
    case "detail_export":
        $bo->detail_export();
        break;
    case "del_payment":
        $id = paramUtils::intByGET("id",false);
        $bo->del_payment($id);
        break;
    case "do_del_payment":
        $id = paramUtils::intByGET("id",false);
        $bo->do_del_payment($id);
        break;
    case "refill":
        $id = paramUtils::intByGET("id",false);
        $bo->refill_view($id);
        break;
    case "do_refill":
        $bo->do_refill();
        break;
    case "service_online":
        $bo->service_online();
        break;
    case "do_service_online":
        $bo->do_service_online();
        break;
    case "tpl_down":
        $bo->tpl_down();
        break;
    case "log_export":
        $bo->log_export();
        break;
}