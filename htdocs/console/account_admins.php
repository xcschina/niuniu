<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");

BO('account_admins');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new account_admins();
    switch ($act){
        case "admins":
            $bo->admins();
            break;
        case "add":
            $bo->add();
            break;
        case "edit":
            $bo->edit($id);
            break;
        case "save":
            $bo->insert();
            break;
        case "update":
            $bo->update();
            break;
        default:
            break;
}