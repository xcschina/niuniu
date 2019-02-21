<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('partner78871_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new partner78871_admin();
    switch ($act){
        case"list":
            $bo->list_view();
            break;
        case"refresh":
            $bo->refresh();
            break;
        case "edit":
            $bo->edit_view($id);
            break;
        case"do_edit":
            $bo->do_edit($id);
            break;
        default:
            break;
}