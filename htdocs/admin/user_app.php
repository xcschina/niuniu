<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('user_app_admin');

$act = paramUtils::strByGET("act", false);

$bo = new user_app_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"export":
        $bo->export();
        break;
}