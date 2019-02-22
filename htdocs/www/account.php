<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("account_web");
COMMON('paramUtils');
$bo = new account_web();
//$bo->open_debug();
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
    case 'register_66173test' :
        $type='66173test';
        $bo->register($type);
        break;
    case 'user_center':
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
    case'register_sms_pc':
        $bo->register_sms();
        break;
    case 'register_voice_pc':
        $bo->register_voice();
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
    case 'qq_login':
        $bo->qq_login_view();
        break;
    case'check_IDcard_view':
        $bo->check_IDcard_view();
        break;
    case'check_IDcard_do':
        $id   = paramUtils::strByPOST("id");
        $name = paramUtils::strByPOST("name");
        $bo->check_IDcard_do($id, $name);
        break;
    case 'register_verify_code' :
        $bo->register_verify_code();
        break;
//    case 'add_user':
//        $num = paramUtils::intByGET("num",false);
//        $key = paramUtils::strByGET("key",false);
//        $bo->add_user($num,$key);
//        break;
}