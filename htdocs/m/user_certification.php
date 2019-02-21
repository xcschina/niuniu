<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('user_certification_mobile');

$bo  = new user_certification_mobile();
$act = paramUtils::strByGET("act");
switch($act){
    case'user_certification':
        $bo->user_certification();
        break;
    case'set_pay_passward':
        $bo->set_pay_passward();
        break;
    case're_set_pay_passward':
        $bo->re_set_pay_passward();
        break;
    case'set_security_mobile':
        $bo->set_security_mobile();
        break;
    case'set_pay_account':
        $bo->set_pay_account();
        break;
    case'send_sms':
        $bo->register_sms();
        break;
    case'get_user_withdraw_info':
        $bo->get_user_withdraw_info();
        break;
    case'get_user_balance_detail':
        $bo->get_user_balance_detail();
        break;
    case 'get_user_info':
        $bo->get_user_info();
        break;
    case 'put_up_withdraw':
        $bo->put_up_withdraw();
        break;
    case 'my_income':
        $bo->my_income();
        break;
    case 'withdraw_view':
        $bo->withdraw_view();
        break;
    case 'user_with_draw_success':
        $bo->user_with_draw_success();
        break;

}