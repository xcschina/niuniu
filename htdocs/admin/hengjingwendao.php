<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('hengjingwendao_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new hengjingwendao_admin();
    switch ($act){
        case"list":
            $bo->hengjingwendao_list_view();
            break;
        case"add":
            $bo->hengjingwendao_add_view();
            break;
        case"do_add":
            $bo->do_add();
            break;
        case "export":
            $bo->export();
            break;
        default:
            sleep(10);
            break;
    }