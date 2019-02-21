<?php
header("Content-Type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
require_once 'config.php';
COMMON("paramUtils");
BO('api_web');
$act = paramUtils::strByGET("act", false);

$bo = new api_web();
switch ($act) {
    case"banner":
        $bo->get_banner();
        break;
    case"hot_game":
        $bo->hot_game();
        break;
    case"hot_more":
        $bo->hot_more();
        break;
    case"hot_search":
        $bo->hot_search();
        break;
    case"search":
        $bo->search();
        break;
    case"go_search":
        $game_name = paramUtils::strByGET("game_name", false);
        $bo->go_search($game_name);
        break;
    case"search_top":
        $bo->top_game();
        break;
    case"game_detail":
        $game_id = paramUtils::intByGET("game_id", false);
        $bo->game_detail($game_id);
        break;
    case"detail_similar":
        $game_id = paramUtils::intByGET("game_id", false);
        $bo->detail_similar($game_id);
        break;
    case"detail_top":
        $game_id = paramUtils::intByGET("game_id", false);
        $bo->detail_top($game_id);
        break;
    case"new_game":
        $bo->new_game();
        break;
    case"new_more":
        $bo->new_more();
        break;
    case"rate_game":
        $bo->rate_game();
        break;
    case"rate_more":
        $bo->rate_more();
        break;
    case"top_game":
        $bo->top_game();
        break;
    case"top_more":
        $bo->top_more();
        break;
    case"rank_list":
        $bo->rank_list();
        break;
    case"sign":
        $bo->get_sign();
        break;
    case"help_center":
        $bo->help_center();
        break;
    case"user_agreement":
        $bo->user_agreement();
        break;
    case"integral":
        $bo->integral();
        break;
    case"game_list":
        $bo->game_list();
        break;
    case"game_info":
        $id = paramUtils::intByREQUEST("id", false);
        $bo->game_info($id);
        break;
}