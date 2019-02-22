<?php
//我的账号
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("warrant_wx");
$bo = new warrant_wx();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'ylc':
        $app_id = paramUtils::intByGET('app_id',false);
        $channel = paramUtils::intByGET('channel');
        $bo->ylc($app_id,$channel);
        break;
}

