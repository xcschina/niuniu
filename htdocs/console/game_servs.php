<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'off');
COMMON("paramUtils","loginCheck");

BO('game_servs_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new game_servs_web();
switch ($act){
    case"game_servs_list":
        $bo->get_game_servs_list();
        break;
    case"serv_add_view":
        $bo->serv_add_view();
        break;
    case"do_serv_add":
        $bo->add_game_serv();
        break;
    case"serv_edit_view":
        $bo->serv_edit_view($id);
        break;
    case"do_serv_edit":
        $bo->do_serv_edit();
        break;
    case'import_view':
        $bo->import_view();
        break;
    case'do_import':
        $bo->do_import();
        break;
    default:
        $bo->get_game_servs_list();
        break;
}