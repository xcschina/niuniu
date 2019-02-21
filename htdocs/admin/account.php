<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
//ini_set("display_errors","On");
//error_reporting(E_ALL);

BO('account_admin');

$act = paramUtils::strByGET("act", false);

$bo = new account_admin();
switch ($act){
    case"list":
        $bo->account_list_view();
        break;
    case"add":
        $pid = paramUtils::intByGET("id");
        $bo->account_add_view();
        break;
    case"add_guild":
        $bo->account_add_guild();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->account_edit_view($id);
        break;
    case"password":
        $id = paramUtils::intByGET("id", false);
        $bo->account_pwd_view($id);
        break;
    case "do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_edit($id);
        break;
    case "do_add":
        $bo->do_account_add();
        break;
    case "do_add_guild":
        $bo->do_add_guild();
        break;
    case "get_min_guild":
        $bo->get_min_guild();
        break;
    case "do_password":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_password($id);
        break;
    case"menu":
        $id = paramUtils::intByGET("id", false);
        $bo->account_menu_view($id);
        break;
    case"group_menu":
        $id = paramUtils::intByGET("id", false);
        $bo->account_group_menu_view($id);
        break;
    case "do_menu":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_menu($id);
        break;
    case "do_group_menu":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_group_menu($id);
        break;
    case "groups":
        $bo->account_group_view();
        break;
    case "add_groups":
        $bo->add_groups();
        break;
    case "do_add_groups":
        $bo->do_add_groups();
        break;
    case"app":
        $id = paramUtils::intByGET("id", false);
        $bo->account_app_view($id);
        break;
    case"rh_app":
        $id = paramUtils::intByGET("id", false);
        $bo->account_rh_app_view($id);
        break;
    case"do_app":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_app($id);
        break;
    case"do_rh_app":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_rh_app($id);
        break;
    case"del":
        $id = paramUtils::intByGET("id", false);
        $bo->del_account($id);
        break;
    case"do_del":
        $bo->do_del();
        break;
    case"add_img":
        $id = paramUtils::intByGET("id", false);
        $bo->add_img($id);
        break;
    case"do_add_img":
        $id = paramUtils::intByGET("id", false);
        $bo->do_add_img($id);
        break;
    case"batch_view":
        $bo->batch_view();
        break;
    case"batch_set":
        $bo->batch_set();
        break;
    case"batch_save":
        $bo->batch_save();
        break;

//    case"guild_menu":
//        $bo->guild_menu();
//        break;
    case"pay_mode":
        $id = paramUtils::intByGET("id", false);
        $bo->pay_mode($id);
        break;
    case "do_pay_mode":
        $id = paramUtils::intByGET("id", false);
        $bo->do_pay_mode($id);
        break;
    default:
        $bo->account_list_view();
        break;
}