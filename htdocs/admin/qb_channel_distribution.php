<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors", "off");

BO('qb_channel_distribution_admin');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");
$bo  = new qb_channel_distribution_admin();
switch($act){
    case"list":
        $bo->list_view();
        break;
    case"distribution":
        $bo->distribution($id);
        break;
    case'do_distribution':
        $bo->do_distribution();
        break;
    case"financial_distribution":
        $bo->financial_distribution($id);
        break;
    case'do_financial_distribution':
        $bo->do_financial_distribution();
        break;
    case"public_financial_distribution":
        $bo->public_financial_distribution($id);
        break;
    case"voucher":
        $bo->voucher($id);
        break;
    case'do_voucher':
        $bo->do_voucher();
        break;
    case"authorization":
        $bo->authorization($id);
        break;
    case'do_authorization':
        $bo->do_authorization();
        break;
    case"refund_view":
        $bo->refund_view($id);
        break;
    case"do_refund":
        $bo->do_refund();
        break;
    case"order_export":
        $bo->order_export();
        break;
    case"get_service":
        $bo->get_service();
        break;
    default:
        $bo->list_view();
        break;
}