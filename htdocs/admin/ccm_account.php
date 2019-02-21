<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","off");

BO('account_admin');

$act = paramUtils::strByGET("act", false);

$bo = new account_admin();
switch ($act){
    case"list":
        $bo->ccm_list_view();
        break;
    case"add":
        $bo->ccm_add_view();
        break;
    case "do_add":
        $bo->ccm_do_add();
        break;
    case "recharge":
        $bo->recharge();
        break;
    case "do_recharge":
        $bo->do_recharge();
        break;
    case "app_service":
        $id = paramUtils::intByGET("id", false);
        $bo->app_service_view($id);
        break;
    case "service_show":
        $bo->service_show();
        break;
    case "service_save":
        $bo->service_save();
        break;
    case "payment_method":
        $user_id = paramUtils::intByGET('id', false);
        $bo->payment_method($user_id);
        break;
    case "do_payment":
        $bo->do_payment();
        break;
    case "inside_add":
        $bo->inside_add();
        break;
    case "do_add_inside":
        $bo->do_add_inside();
        break;
    case "get_min_guild":
        $bo->get_chamber_min_guild();
        break;
    case "set_state":
        $id = paramUtils::intByGET('id',false);
        $bo->set_state($id);
        break;
    case "do_state":
        $bo->do_state();
        break;
}