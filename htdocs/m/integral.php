<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
BO("integral_mobile");
COMMON('paramUtils');
$bo = new integral_mobile();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'index':
        $app_id = paramUtils::intByGET('app_id',false);
        $channel = paramUtils::strByGET("channel");
        $u_id = paramUtils::intByGET('u_id');
        $sex = paramUtils::strByGET('sex');
        $headimgurl = paramUtils::strByGET('headimgurl');
        $sign = paramUtils::strByGET('sign');
        $bo->index_view($app_id,$channel,$u_id,$sex,$headimgurl,$sign);
        break;
    case'do_login':
        $bo->do_login();
        break;
    case'sms_code':
        $bo->sms_code();
        break;
    case'bind_mobile':
        $bo->bind_mobile();
        break;
    case'wx_warrant':
        $bo->wx_warrant();
        break;
    case'logout':
        $bo->logout();
        break;
    case'draw_list':
        $bo->draw_list();
        break;
    case'exchange':
        $bo->exchange();
        break;
    case "device":
        $bo->add_device();
        break;
    case "login":
        $bo->add_login();
        break;
    case "login_registered":
        $bo->login_registered();
        break;
}