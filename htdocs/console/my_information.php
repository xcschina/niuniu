<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('my_information');

$act = paramUtils::strByGET("act", false);
$bo = new my_information();
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    switch ($act){
        case "information":
            $bo->info_view();
            break;
        default:
            $bo->info_view();
            break;
    }
}else{
    switch ($act){
        case "information":
            $bo->info_view();
            break;
    }
}