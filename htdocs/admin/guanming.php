<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('guanming_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new guanming_admin();
    switch ($act){
        case"list":
            $bo->guanming_list_view();
            break;
        case"add":
            $bo->guanming_add_view();
            break;
        case"do_add":
            $bo->do_add();
            break;
        default:
            sleep(10);
            break;
}