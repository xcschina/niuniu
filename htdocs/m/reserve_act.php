<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("reserve_web");
COMMON('paramUtils');
//ini_set("display_errors","On");
//error_reporting(E_ALL);
$bo = new reserve_web();
$act = paramUtils::strByGET("act",false);
$act_id = paramUtils::strByGET("act_id");
$code = paramUtils::strByGET("code");
switch ($act) {
    case'index':
        $bo->index($act_id,$code);
        break;
    //预约
    case'reserve':
        $bo->reserve();
        break;
    //登录
    case'do_login':
        $bo->do_login();
        break;
    //验证码
    case'sms_code':
        $bo->sms_code();
        break;
    //抽奖
    case'draw':
        $bo->draw();
        break;
    //抽奖功能
    case'go_draw':
        $user_id = paramUtils::strByGET("user_id");
        $act_id = paramUtils::strByGET("act_id");
        $bo->go_draw($user_id,$act_id);
        break;
    case'my_gift':
        $bo->my_gift();
        break;
        //礼包记录修正
//    case'correct':
//        $act_id = paramUtils::strByGET("act_id");
//        $bo->correct($act_id);
//        break;
}