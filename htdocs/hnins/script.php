<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('script_admin');
$bo = new script_admin();
$act = paramUtils::strByGET("act", false);
switch($act){
    case"seal_off_user_device":
        $admin = paramUtils::strByGET('user',false);
        $app_id = paramUtils::strByGET('app_id',false);
        $channel = paramUtils::strByGET('channel',false);
        $bo->seal_off_user_device($admin,$app_id,$channel);
        break;
    case"app_user_retention":
        $app_id = paramUtils::strByGET('app_id',false);
        $channel = paramUtils::strByGET('channel',false);
        $admin = paramUtils::strByGET('user',false);
        $bo->app_user_retention($admin,$app_id,$channel);
        break;
}