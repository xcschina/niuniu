<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");

BO('feedback_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new feedback_admin();
switch ($act){
    case "view":
        $bo->list_view();
        break;
    case "edit":
        $bo->edit_view($id);
        break;
    case "do_edit":
        $bo->do_edit($id);
        break;
    case "account_retrieve":
        $bo->account_retrieve();
        break;
    case "account_edit":
        $bo->account_edit($id);
        break;
    case "do_account_edit":
        $bo->do_account_edit($id);
        break;
    case "mobile_change":
        $bo->mobile_change();
        break;
    //增加身份证换绑
    case "idCard_change":
        $bo->idCard_change();
        break;
    case "add_change":
        $bo->add_change();
        break;
    case "sms_code":
        $bo->sms_code();
        break;
    case "sec_sms_code":
        $bo->sec_sms_code();
        break;
    case "account_verify":
        $bo->account_verify();
        break;
    case "do_change":
        $bo->do_change();
        break;
    //增加身份证验证
    case "reg_idCard":
        $bo->reg_idCard();
        break;
    //增加身份证图片保存
    case "do_idCard":
        $bo->do_idCard();
        break;
    default:
        $bo->list_view();
        break;
    case "change_pwd":
        $bo->change_pwd();
        break;
    case "change_user_pwd":
        $bo->change_user_pwd();
        break;
    case "reg_user_id":
        $bo->reg_user_id();
        break;
    case "do_change_pwd":
        $bo->do_change_pwd();
        break;
    case "update_status":
        $bo->update_status();
        break;
    case"new_list":
        $bo->new_list();
        break;
    case'new_edit':
        $bo->new_edit($id);
        break;
    case'do_new_edit':
        $bo->do_new_edit($id);
        break;
}