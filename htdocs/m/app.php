<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("app_admin");
COMMON('paramUtils');
$act = paramUtils::strByGET("act",false);
$bo = new app_admin();
switch ($act) {
    case'app_share_detail':
        $game_id = paramUtils::strByGET("game_id",false);
        $bo->app_share_detail($game_id);
        break;
    case 'send_code':
        $app_id = paramUtils::intByGET('app_id',false);
        $bo->send_code($app_id);
        break;

}