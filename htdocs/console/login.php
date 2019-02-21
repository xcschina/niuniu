<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('login_web');

$act = paramUtils::strByGET("act", false);
$bo = new login_web();
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    switch ($act){
        case "login_view":
            $bo->login_view();
            break;
        case "do_logout":
            $bo->do_logout();
            break;
        default:
            $bo->login_view();
            break;
    }
}else{
    switch ($act){
        case "do_login":
            $bo->do_login();
            break;
    }
}