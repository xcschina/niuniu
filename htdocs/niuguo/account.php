<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","ON");
BO("account_admin");
COMMON('paramUtils');
$act = paramUtils::strByGET("act", false);
$bo = new account_admin();
switch ($act) {
    case'register':
        $bo->register();
        break;
    case'sms_code':
        $bo->sms_code();
        break;
    case'do_register':
        $bo->do_register();
        break;
    case'login':
        $bo->login();
        break;
    case'user_center':
        $bo->user_center();
        break;
    case'do_login':
        $bo->do_login();
        break;
    case'password_forget':
        $bo->password_forget();
        break;
    case'verify_mobile':
        $bo->verify_mobile();
        break;
    case'set_password':
        $bo->set_password();
        break;
    case'real_verify':
        $bo->real_name_verify();
        break;
    case'do_real_verify':
        $bo->do_real_verify();
        break;
    case'do_user_info':
        $bo->do_user_info();
        break;
    case'phone_bind':
        $bo->phone_bind();
        break;
    case'logout':
        $bo->logout();
        break;
    default:
        $bo->user_center();
        break;
}