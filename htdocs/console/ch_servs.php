<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('game_servs_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id",false);

$bo = new game_servs_web();
switch ($act){
    case 'view':
        $bo->ch_servs($id);
        break;
    case 'edit':
        $ch_id = paramUtils::intByGET("ch_id",false);
        $bo->ch_serv_edit($id,$ch_id);
        break;
    case "save":
        $ch_id = paramUtils::intByGET("ch_id",false);
        $bo->ch_serv_save($id, $ch_id);
        break;
    case'save':
        print_r($_POST);
        print_r($_GET);
        break;
    default:
        $bo->get_game_servs_list();
        break;
}