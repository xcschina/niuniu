<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");

BO('account_groups');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new account_groups();
    switch ($act){
        case "groups":
            $bo->groups();
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