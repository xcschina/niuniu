<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");

BO('web_rank');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new web_rank();
    switch ($act){
        case "hot_rank":
            $bo->hot_rank_view();
            break;
        case "hot_edit":
            $bo->hot_edit($id);
            break;
        case "hot_add":
            $bo->hot_add();
            break;
        case "hot_update":
            $bo->hot_update($id);
            break;
        case "hot_save":
            $bo->hot_save();
            break;
        case "new_rank":
            $bo->new_rank_view();
            break;
        case "new_game_rank_add":
            $bo->new_game_rank_add();
            break;
        case "new_game_rank_save":
            $bo->new_game_rank_save();
            break;
        case "new_game_rank_edit":
            $bo->new_game_rank_edit($id);
            break;
        case "new_game_rank_update":
            $bo->new_game_rank_update($id);
            break;
        default:
            break;
}