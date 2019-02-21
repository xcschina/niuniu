<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set('display_errors', 'On');
COMMON("paramUtils", "loginCheck");
BO('coupon_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new coupon_web();
switch ($act) {
    case"coupon_list":
        $bo->coupon_list();
        break;
    case"coupon_add":
        $bo->coupon_add();
        break;
    case"coupon_edit":
        $bo->coupon_edit($id);
        break;
    case"coupon_review":
        $bo->coupon_review($id);
        break;
    case"coupon_show":
        $bo->coupon_show($id);
        break;
    case"coupon_save":
        $bo->coupon_save();
        break;
    case"coupon_update":
        $bo->coupon_update();
        break;
    case"review_status":
        $bo->review_status();
        break;
    case"issue_status":
        $bo->issue_status();
        break;
    case"coupon_hide":
        $bo->coupon_hide($id);
        break;
    case"coupon_log":
        $bo->coupon_log($id);
        break;
    case"send_view":
        $bo->send_view($id);
        break;
    case"send_coupon":
        $bo->send_coupon($id);
        break;
    case"details":
        $bo->details($id);
        break;
    default:
        break;
}