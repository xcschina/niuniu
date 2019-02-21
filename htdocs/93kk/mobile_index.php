<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
ini_set("display_errors","on");

BO('mobile_index_admin');

$act = paramUtils::strByGET("act");
$id = paramUtils::intByGET("id");

$bo = new mobile_index_admin();
switch ($act){
    case "index":
        $bo->index_view();
        break;
    case "more_game":
        $bo->get_more_game_list();
        break;
    case "game_detail":
        $bo->detail_view($id);
        break;
    case "enter_view":
        $bo->enter_view();
        break;
    default:
        $bo->enter_view();
}