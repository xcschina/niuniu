<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
BO("activity_mobile");
COMMON('paramUtils');
$bo = new activity_mobile();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'ajax_get_gift':
        $bo->ajax_get_gift();
        break;
    case'ajax_get_coupon':
        $bo->ajax_get_coupon();
        break;
    case'ajax_draw_acticity':
        $bo->ajax_draw_activity();
        break;
    case'ajax_share_activity':
        $bo->ajax_share_activity();
        break;

    default:
        $bo->activity_view();
        break;
}