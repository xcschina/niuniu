<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('app_admin','auto_pack_admin');
$act = paramUtils::strByGET("act", false);

$bo = new app_admin();
$pack = new auto_pack_admin();
switch ($act){
    case"list":
        $bo->app_list_view();
        break;
    case"guild_list":
        $bo->guild_list_view();
        break;
    case"pack_prompt":
        $app_id = paramUtils::intByGET("id", false);
        $bo->pack_prompt_view($app_id);
        break;
    case"do_pack":
        $app_id = paramUtils::intByGET("app_id",false);
        $bo->do_pack($app_id);
        break;
    case"do_pack_app":
        $app_id = paramUtils::intByGET("app_id", false);
//        $bo->new_pack($app_id);
        $pack->do_pack_app($app_id);
        break;
    case"add_pack":
        $app_id = paramUtils::intByGET("app_id", false);
        $bo->add_pack($app_id);
        break;
    case"to_apk_pack":
        $bo->to_apk_pack();
        break;
    case"add":
        $bo->app_add_view();
        break;
    case"edit":
        $id = paramUtils::intByGET("id", false);
        $bo->app_edit_view($id);
        break;
    case"edit_notice":
        $id = paramUtils::intByGET("id", false);
        $bo->app_notice_edit_view($id);
        break;
    case"version_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->version_edit($id);
        break;
    case "version_update":
        $id = paramUtils::intByGET("id", false);
        $bo->version_update($id);
        break;
    case "do_edit":
        $id = paramUtils::intByGET("id", false);
        $bo->do_app_edit($id);
        break;
    case "do_edit_notice":
        $id = paramUtils::intByGET("id", false);
        $bo->do_app_notice_edit($id);
        break;
    case "do_add":
        $bo->do_app_add();
        break;
    case "yyq_pack":
        $app_id = paramUtils::intByGET("app_id", false);
        $bo->yyq_pack($app_id);
        break;
    case "real_validate":
        $id = paramUtils::intByGET("id", false);
        $bo->real_validate_view($id);
        break;
    case "real_validate_save":
        $id = paramUtils::intByGET("id", false);
        $bo->real_validate_save($id);
        break;
    case "app_discount_edit":
        $app_id = paramUtils::intByGET("app_id",false);
        $bo->app_discount_edit($app_id);
        break;
    case "app_discount_save":
        $bo->app_discount_save();
        break;
    case"web":
        $id = paramUtils::intByGET("id", false);
        $bo->web($id);
        break;
    case"web_save":
        $id = paramUtils::intByGET("id",false);
        $bo->web_save($id);
        break;
    case"sdk_verify_rules":
        $bo->sdk_verify_rules_list();
        break;
    case"sdk_rules_add":
        $bo->sdk_rules_add();
        break;
    case"sdk_rules_save":
        $bo->sdk_rules_save();
        break;
    case"app_verify_edit":
        $app_id = paramUtils::intByGET("app_id",false);
        $bo->app_verify_edit($app_id);
        break;
    case"app_rules_save":
        $bo->app_rules_save();
        break;
    case "app_verify_code_edit":
        $app_id = paramUtils::intByGET("app_id",false);
        $bo->app_verify_code_edit($app_id);
        break;
    case "app_verify_code_save":
        $bo->app_verify_code_save();
        break;
    case"export":
        $bo->export();
        break;
    case"apple_list":
        $app_id = paramUtils::intByGET("app_id", false);
        $bo->apple_list($app_id);
        break;
    case"do_add_apple":
        $bo->do_add_apple();
        break;
    case"apple_edit":
        $bo->apple_edit();
        break;
    case"do_edit_apple":
        $bo->do_edit_apple();
        break;
    case"upload_img":
        $bo->upload_img();
        break;
    case"do_edit_apple_notice":
        $bo->do_edit_apple_notice();
        break;
    case"callback":
        $bo->callback();
        break;
    default:
        $bo->app_list_view();
        break;
}