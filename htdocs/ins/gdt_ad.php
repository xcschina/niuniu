<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("gdt_ad_active");
$bo = new gdt_ad_active();
$act = paramUtils::strByGET("act",false);
switch ($act) {
    case "android":
        $bo->android_feedback();
        break;
    case "ios":
        $bo->ios_feedback();
        break;
}
