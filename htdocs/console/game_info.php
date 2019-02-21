<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('game_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new game_info_web();
switch ($act){
    case'game_list':
        $bo->get_game_list();
        break;
    case'game_add_view':
        $bo->game_add_view();
        break;
    case'do_game_add':
        $bo->add_game();
        break;
    case'game_edit_view':
        $bo->game_edit_view($id);
        break;
    case'do_game_edit':
        $bo->edit_game();
        break;
    case'channel_view':
        $bo->channel_view($id);
        break;
    case'del_game':
        $bo->del_game($id);
        break;
    case'import_view':
        $bo->game_import_view();
        break;
    case'do_import':
        $bo->do_import();
        break;
    case'do_save_ch':
        $bo->do_save_ch();
        break;
    case'game_type_list':
        $bo->game_type_list();
        break;
    case'hot_game_list':
        $bo->hot_game_list();
        break;
    case'hot_game_edit_view':
        $bo->hot_game_edit_view();
        break;
    case'save_hot_games':
        $bo->save_hot_games();
        break;
    case'config_view':
        $bo->game_config_view($id);
        break;
    case'game_introduce':
        $bo->game_introduce($id);
        break;
    case'do_config_save':
        $bo->do_config_save();
        break;
    case'do_introduce_save':
        $bo->do_introduce_save();
        break;
    case'game_download_list';
        $bo->game_download_list();
        break;
    case'game_download_add_view':
        $bo->game_download_add_view();
        break;
    case'do_game_download_add':
        $bo->do_game_download_add();
        break;
    case'game_download_edit_view':
        $bo->game_download_edit_view($id);
        break;
    case'do_game_download_edit':
        $bo->do_game_download_edit();
        break;
    case'hot_game_remove':
        $bo->hot_game_remove();
        break;
    case'export':
        $bo->export();
        break;
    default:
        $bo->get_game_list();
        break;
}