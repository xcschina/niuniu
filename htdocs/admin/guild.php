<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('guild_admin');

$act = paramUtils::strByGET("act", false);
$bo = new guild_admin();

switch ($act){
    case "guild_list":
        $bo->guild_list();
        break;
    case "do_recharge":
        $bo->do_recharge();
        break;
    case "recharge":
        $parent_id = paramUtils::intByGET("parent_id", false);
        $son_id = paramUtils::intByGET("son_id", false);
        $type = paramUtils::intByGET("type", false);
        $bo->recharge_list($parent_id, $son_id, $type);
        break;
    case "frozen":
        $parent_id = paramUtils::intByGET("parent_id", false);
        $son_id = paramUtils::intByGET("son_id", false);
        $type = paramUtils::intByGET("type", false);
        $bo->frozen_view($parent_id, $son_id,$type);
        break;
    case "revoke":
        $parent_id = paramUtils::intByGET("parent_id", false);
        $son_id = paramUtils::intByGET("son_id", false);
        $type = paramUtils::intByGET("type", false);
        $bo->revoke_view($parent_id, $son_id,$type);
        break;
    case "revoke_log":
        $id = paramUtils::intByGET("id", false);
        $bo->revoke_log($id);
        break;
    case "do_revoke":
        $bo->do_revoke();
        break;
    case "do_frozen":
        $bo->do_frozen();
        break;
    case "password":
        $id = paramUtils::intByGET("id", false);
        $bo->account_pwd_view($id);
        break;
    case "do_password":
        $id = paramUtils::intByGET("id", false);
        $bo->do_account_password($id);
        break;
    case "pay_list":
        $bo->pay_list();
        break;
    case "export":
        $bo->export();
        break;
    case "nnb_recharge":
        $bo->nnb_recharge();
        break;
    case "nnb_export":
        $bo->nnb_export();
        break;
    case "add_nnb":
        $bo->add_nnb();
        break;
    case "do_add_nnb":
        $bo->do_add_nnb();
        break;
    case "reason_view":
        $id = paramUtils::intByGET("id", false);
        $bo->reason_view($id);
        break;
    case "do_review":
        $id = paramUtils::intByGET("id", false);
        $bo->do_review($id);
        break;
    case "user_list":
        $bo->user_list();
        break;
    case"pack_list":
        $bo->pack_list();
        break;
    case"pack_status":
        $id = paramUtils::intByGET("id",false);
        $bo->pack_status($id);
        break;
    case"status_save":
        $bo->status_save();
        break;
    case"pack_validate":
        $bo->pack_validate();
        break;
    case"validate_save":
        $bo->validate_save();
        break;
    case "user_revoke_log_list":
        $bo->user_revoke_log_view();
        break;
    case "user_revoke_list":
        $bo->user_revoke_view();
        break;
    case "user_search":
        $bo->user_search_info();
        break;
    case "user_revoke_do":
        $bo->user_revoke_do();
        break;
    case "create_code":
        $bo->create_verifi();
        break;
    case "user_revoke_msg":
        $id = paramUtils::intByGET("id",false);
        $bo->user_revoke_msg($id);
        break;
    case "guild_data_sup_count":
        $bo->guild_data_sup_count_list();
        break;
    case "guild_data_sub_count":
        $id = paramUtils::intByGET("id",false);
        $bo->guild_data_sub_count_list($id);
        break;
    case "guild_data_child_count":
        $id = paramUtils::intByGET("id",false);
        $bo->guild_data_child_count_list($id);
        break;
    case "apps_data":
        $bo->apps_data_list();
        break;
    case "app_channel_data":
        $app_id = paramUtils::intByGET("app_id",false);
        $bo->app_channel_data($app_id);
        break;
    case "nd_user_info":
        $bo->nd_user_info();
        break;
    case "nd_user_charge":
        $bo->nd_user_charge();
        break;
    case "nd_user_verify":
        $bo->nd_user_verify();
        break;
    case "nd_user_charge_save":
        $bo->nd_user_charge_save();
        break;
    case "nd_user_frozen":
        $bo->nd_user_frozen();
        break;
    case "nd_user_frozen_save":
        $bo->nd_user_frozen_save();
        break;
    case "nd_operation_log":
        $bo->nd_operation_log();
        break;
    case "nd_user_revoke":
        $bo->nd_user_revoke();
        break;
    case "nd_user_revoke_save":
        $bo->nd_user_revoke_save();
        break;
    case "export_nd_log":
        $bo->export_nd_log();
        break;
}