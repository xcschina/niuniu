<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index_mobile');
$bo = new index_mobile();

$act = paramUtils::strByGET("act");
$bo->games_view();
//switch ($act) {
//    case 'allgames':
//        $letter = paramUtils::strByGET("letter",false);
//        $bo->letter_games($letter);
//    default:
//        $bo->games_view();
//        break;
//}