<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('yzm_admin');
$act = paramUtils::strByGET("act", false);
$bo = new yzm_admin();
switch ($act){
    case"list":
        $bo->list_view();
        break;
    case"add":
        $type = paramUtils::intByGET('type');
        $bo->add_view($type);
        break;
    case"do_add":
        $bo->do_add_private();
        break;
    case"export":
        $bo->export();
        break;
    case"private_list":
        $bo->private_list();
        break;
}