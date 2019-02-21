<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('login_admin');
$act = paramUtils::strByGET("act");
$bo = new login_admin();
//$bo->open_debug();
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    switch ($act){
        case "login_view":
            $bo->login_view();
            break;
        case "do_logout":
            $bo->do_logout();
            break;
        case "login":
            $bo->login();
            break;
        case "logout":
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