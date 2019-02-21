<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('user_admin');

$act = paramUtils::strByGET("act", false);

$bo = new user_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"change_pwd":
        $bo->change_pwd();
        break;
    case"reg_user_id":
        $bo->reg_user_id();
        break;
    case"reg_mobile":
        $bo->reg_mobile();
        break;
    case"do_change_pwd":
        $bo->do_change_pwd();
        break;
    case"clear_mobile":
        $bo->clear_mobile();
        break;
    case"do_clear_mobile":
        $bo->do_clear_mobile();
        break;
    case"qa_clear":
        $bo->qa_clear();
        break;
    case"do_qa_clear":
        $bo->do_qa_clear();
        break;
    case "suspend_list":
        $bo->suspend();
        break;
    case "do_suspend":
        $bo->do_suspend();
        break;
    case "reg_suspend":
        $bo->reg_suspend();
        break;
    case "do_suspend_save":
        $bo->do_suspend_save();
        break;
    case "relieve_suspend":
        $bo->relieve_suspend();
        break;
    case "relieve_suspend_save":
        $bo->relieve_suspend_save();
        break;
    case "relieve_suspend_bind":
        $bo->relieve_suspend_bind();
        break;
    case "relieve_suspend_bind_save":
        $bo->relieve_suspend_bind_save();
        break;
    case "search_user_info":
        $bo->search_user_info();
        break;

}