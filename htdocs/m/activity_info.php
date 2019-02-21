<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","Off");
BO("activity_info_mobile");
COMMON('paramUtils');
$bo = new activity_info_mobile();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'activity_view':
        $id=paramUtils::intByGET("id",false);
        $bo->activity_view($id);
        break;
    case'activity_ajax':
        $bo->activity_ajax();
        break;
}