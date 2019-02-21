<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_mobile');

$bo = new index_mobile();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'get_games':
        $letter = paramUtils::strByGET("letter",false);
        $type = paramUtils::strByGET("type",false);
        $bo->letter_games($letter, $type);
        break;
    case'keyword':
        $bo->get_keyword_game();
        break;
    case 'reg_guide':
        $bo->get_reg_guid();
        break;
    case 'game_list':
        $letter=$_GET['letter'];
        $bo->get_now_gamelist($letter);
        break;
//    case'up_refill_rate':
//        $bo->up_refill_rate();
//        break;
    case'main_search':
        $bo->main_search();
        break;
    case'password':
        $bo->password();
        break;
    case'ajax_list':
        $bo->ajax_list();
        break;
    default:
        $bo->index_view();
        break;
}