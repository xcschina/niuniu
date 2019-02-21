<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("guild_reserve");
COMMON('paramUtils');
//ini_set("display_errors","On");
//error_reporting(E_ALL);
$bo = new guild_reserve();
$act = paramUtils::strByGET("act",false);
$act_id = paramUtils::strByGET("id");
switch ($act) {
    case'index':
        $bo->index($act_id);
        break;
    case'my_gift':
        $bo->my_gift();
        break;
    case'reserve': //预约
        $bo->reserve();
        break;
    case'do_login':
        $bo->do_login();
        break;
    //验证码
    case'sms_code':
        $bo->sms_code();
        break;
}