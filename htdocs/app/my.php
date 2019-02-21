<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('my_web');
$act = paramUtils::strByGET("act", false);
$bo = new my_web();
switch ($act){
    case "my_order":
        $bo->my_order();
        break;
    case "more_order":
        $bo->more_order();
        break;
    case "my_gift":
        $bo->my_gift();
        break;
    case "more_gift":
        $bo->more_gift();
        break;
    default:
        break;
}