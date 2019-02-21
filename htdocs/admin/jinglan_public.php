<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('jinglan_public_admin');

$act = paramUtils::strByGET("act", false);
$bo = new jinglan_public_admin();
    switch ($act){
        case"list":
            $bo->list_view();
            break;
        case"add":
            $bo->add_view();
            break;
        case"do_add":
            $bo->do_add();
            break;
        case"export":
            $bo->export();
            break;
        case'price_edit':
            $id = paramUtils::intByGET('id',false);
            $bo->price_edit($id);
            break;
        case'price_save':
            $bo->price_save();
            break;
        default:
            sleep(10);
            break;
}