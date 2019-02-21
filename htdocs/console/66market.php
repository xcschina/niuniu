
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('market_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");
$bo = new market_admin();
switch ($act){
    case'app_banner':
        $bo->banner_list();
        break;
    case'add_banner':
        $bo->add_banner();
        break;
    case'banner_save':
        $bo->banner_save();
        break;
    case"del_banner":
        $bo->del_banner($id);
        break;
    case"game_list":
        $bo->game_list();
        break;
    case"add_game":
        $bo->add_game();
        break;
    case"game_add_view":
        $bo->game_add_view();
        break;
    case"del_game":
        $bo->del_game($id);
        break;
    case"game_edit_view":
        $bo->game_edit_view($id);
        break;
    case"edit_game":
        $bo->edit_game();
        break;
}