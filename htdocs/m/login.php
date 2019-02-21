<?php
header("Content-Type: text/html; charset=utf-8");

require_once 'config.php';

ini_set("display_errors","Off");

BO('login_mobile');

COMMON('paramUtils');
$bo = new login_mobile();
$act = paramUtils::strByGET("act",false);


switch ($act) {
    default:
        $bo->user_center();
        break;
    case'login':
        $bo->login();
        break;
    case'forget':
        $bo->forget();
        break;
    case 'register':
        $bo->register();
        break;
    case 'user_center':
        $bo->user_center();
        break;
    case'setting_view':
        $bo->user_center();
        break;
    case'modify_psw':
        $bo->modify_psw();
        break;
//    case'mobile_info':
//        $bo->modify_phone("1");
//        break;
    case'modify_phone':
        $bo->modify_phone("2");
        break;
    case'email_info':
        $bo->modify_email("1");
        break;
    case'modify_email':
        $bo->modify_email("2");
        break;
    case'modify_user':
        $bo->modify_user();
        break;
    case'do_login':
        $bo->do_login();
        break;
    case'register_sms_m':
        $bo->register_sms();
        break;
    case'email_sms':
        $bo->email_code();
        break;
    case'do_register':
        $bo->do_register();
        break;
    case"do_forget";
        $bo->do_forget();
        break;
    case"do_modify_psw":
        $bo->do_modify_psw();
        break;
    case'do_modify_phone':
        $bo->do_modify_phone();
        break;
    case'do_qqlogin_phone_bind':
        $bo->do_qqlogin_phone_bind();
        break;
    case'do_modify_email':
        $bo->do_modify_email();
        break;
    case'do_modify_user':
        $bo->do_modify_user();
        break;
    case 'logout':
        $bo->logout();
        break;
    case 'my-code':
        $bo->my_code();
        break;
    case "order-list":
        $bo->my_orders();
        break;
    case "order":
        $id = paramUtils::intByGET("id",false);
        $bo->order_view($id);
        break;
    case 'qq_login':
        $bo->qq_login_view();
        break;
}