<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('luoke_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");
$bo = new luoke_admin();
    switch ($act){
        case"list":
            $bo->luoke_list_view();
            break;
        case"add":
            $bo->luoke_add_view();
            break;
        case"do_add":
            $bo->do_add();
            break;
        case"query_balance":
            $bo->query_balance();
            break;
        case"query_recharge":
            $bo->query_recharge();
            break;
        case"luoke_export":
            $bo->luoke_export();
            break;
        case"b_list":
            $bo->luoke_b_list_view();
            break;
        case"b_add":
            $bo->b_add_view();
            break;
        case"do_b_add":
            $bo->do_b_add();
            break;
        case"luoke_b_export":
            $bo->luoke_b_export();
            break;
        default:
            sleep(10);
            break;
}