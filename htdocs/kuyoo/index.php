<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_web');

$bo = new index_web();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'get_games':
        $letter = paramUtils::strByGET("letter",false);
        $bo->get_games($letter);
        break;
    case'keyword':
        $keyword = paramUtils::strByGET("keyword");
        $bo->get_keyword_game($keyword);
        break;
    case'check_sch':
        $sch = paramUtils::strByGET("sch");
        $bo->check_game_user($sch);
    case'check_sch_do':
        $sch = paramUtils::strByPOST("sch");
        $bo->do_check_game_user($sch);
    default:
        $bo->index_web();
        break;
}