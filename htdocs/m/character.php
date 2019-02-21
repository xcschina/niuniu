<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_mobile');
$bo = new index_mobile();
$act = paramUtils::strByGET("act");
switch ($act) {
    case'get_games':
        $platform = paramUtils::strByGET("platform");
        $letter = paramUtils::strByGET("letter");
        $bo->get_games($platform,$letter);
        break;
//    case'up_char_rate':
//        $bo->up_char_rate();
//        break;
    default:
        $bo->character_view();
        break;
}