<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('game_admin');
$act = paramUtils::strByGET("act", false);
$bo = new game_admin();
switch ($act){
    case"list":
        $bo->game_list_view();
        break;
    case "add":
        $bo->game_add_view();
        break;
    case "do_add":
        $bo->game_do_add();
        break;
    case"edit":
        $app_id = paramUtils::intByGET("id", false);
        $bo->game_edit_view($app_id);
        break;
    case "do_edit":
        $bo->game_do_edit();
        break;
}