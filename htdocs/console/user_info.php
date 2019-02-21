<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('user_info_web');

$act = paramUtils::strByGET("act", false);
$id  = paramUtils::intByGET("id");

$bo = new user_info_web();
    switch ($act){
        case"user_info_list":
            $bo->get_user_info_list();
            break;
        case"search_user_info_list":
            $code  = paramUtils::strByGET("code");
            $bo->search_user_info_list($code);
            break;
        case"pay_user_list":
            $bo->get_pay_user_list();
            break;
//        case"pay_user_update": //完善user_info表信息, 更新vip等级,地区信息,年龄信息
//            $bo->pay_user_update();
//            break;
        case"user_info_detail":
            $bo->user_info_detail($id);
            break;
        case"game_info_detail":
            $bo->game_info_detail($id);
            break;
        case"user_info_detail_do":
            $bo->user_info_detail_do($id);
            break;
        case"user_pay_list":
            $bo->user_pay_list($id);
            break;
        case"user_login_list":
            $bo->user_login_list($id);
            break;
        case "login_log_list":
            $bo->get_login_log_list();
            break;
        case "operation_log_list":
            $bo->get_operation_log_list();
            break;
        case "change_channel":
            $bo->change_my_channel();
            break;
//        case"user_age":
//            $bo->user_age();
//            break;
        default:
            break;
}