<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("account_wx");
$bo = new account_wx();
$act = paramUtils::strByGET("act");

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    switch($act) {
        case 'login':
            $bo->login_view();
            break;
        case "register_sms_wx":
            $mobile = paramUtils::strByGET("mobile",false);
            $code = paramUtils::strByGET("code",false);
            $bo->register_sms($mobile, $code);
            break;
        case "info";
            $bo->info_view();
            break;
        default:
            $bo->index_view();
            break;
    }
}else{
    switch($act){
        case 'do-login':
            $bo->do_login();
            break;
        case "mobile-verify":
            $bo->do_mobile_verify();
            break;
        default:
            $bo->index_view();
            break;
    }
}