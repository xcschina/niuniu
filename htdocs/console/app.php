
<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('app_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new app_admin();
switch ($act){
    case'banner_list':
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
    case"play_list":
        $bo->play_list();
        break;
    case"add_play":
        $bo->add_play();
        break;
    case"play_add_view":
        $bo->play_add_view();
        break;
    case"del_play":
        $bo->del_play($id);
        break;
    case"play_edit_view":
        $bo->play_edit_view($id);
        break;
    case"edit_play":
        $bo->edit_play();
        break;
}