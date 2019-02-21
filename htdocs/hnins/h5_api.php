<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
$bo = new game_pay_web();

$act = paramUtils::strByGET("act",false);
switch ($act) {
    case "role":
        $bo->add_role();
        break;
    case "device":
        $bo->add_device();
        break;
    case "login":
        $bo->add_login();
        break;
    case 'bind':
        $bo->bind_mobile();
        break;
    case 'check'://手机验证
        $bo->check_mobile();
        break;
    case 'check_code'://获取验证码
        $bo->check_code();
        break;
    case 'reg_code'://获取验证码
        $bo->reg_code();
        break;
    case 'accountpwd'://密码登录
        $bo->account_pwd();
        break;
    case 'accountcode'://验证码登录
        $bo->account_code();
        break;
    case 'accountreg'://注册
        $bo->account_reg();
        break;
    case 'reset_pwd'://重置密码
        $bo->reset_pwd();
        break;
    case 'log_out'://退出
        $bo->log_out();
        break;
    default;
        die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"找不到服务器"))));
        break;
}
