<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
BO("activity_web");
COMMON('paramUtils');
$bo = new activity_web();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case'activity_view':
        $id = paramUtils::intByGET("id",false);
        $bo->activity_view($id);
        break;
    case'activity_ajax':
        $bo->activity_ajax();
        break;
}