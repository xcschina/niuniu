<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('system_password');

$act = paramUtils::strByGET("act", false);
$bo = new system_password();
if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    switch ($act){
        case "password":
            $bo->password();
            break;
        default:
            $bo->password();
            break;
    }

}else{
    switch($act){
        case "changePassword":
            $bo->changePassword();
            break;
    }
}