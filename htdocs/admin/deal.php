<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
error_reporting(E_ALL);
ini_set("display_errors","on");

BO('deal_admin');

$act = paramUtils::strByGET("act", false);

$bo = new deal_admin();
switch ($act){
    case"list":
        $bo->deal_list_view();
        break;
    case"add":
        $bo->deal_add_view();
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"test":
        $bo->test();
        break;
    default:
        sleep(10);
        break;
}
?>