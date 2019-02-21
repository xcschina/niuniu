<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('game_activity_web');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");
$bo = new game_activity_web();
switch ($act) {
    case'activity_info':
        $bo->activity_info();
        break;
    case'add_activity_view':
        $bo->add_activity_view();
        break;
    case'gift_info':
        $game_id = paramUtils::strByGET("game_id",false);
        $bo->gift_info($game_id);
        break;
    case'add_activity':
        $bo->add_activity();
        break;
    case'game_activity_edit':
        $bo->game_activity_edit($id);
        break;
    case'activity_edit':
        $bo->activity_edit();
        break;
    case'del_activity':
        $bo->del_activity($id);
        break;
}