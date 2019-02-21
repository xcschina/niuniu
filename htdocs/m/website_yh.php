<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","Off");
BO("website_yh");
COMMON('paramUtils');
$bo = new website_yh();
$act = paramUtils::strByGET("act",true);

switch ($act) {
    case'merit':
        $bo->merit();
        break;
    default:
        $bo->merit();

}