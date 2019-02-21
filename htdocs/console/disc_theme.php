<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('disc_theme_admin');
$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");
$bo = new disc_theme_admin();
switch ($act){
    case'theme_list':
        $bo->theme_list();
        break;
    case'add_view':
        $bo->add_view();
        break;
    case'add_save':
        $bo->add_save();
        break;
    case'edit_view':
        $bo->edit_view($id);
        break;
    case'edit_save':
        $bo->edit_save();
        break;
    case'del_theme':
        $bo->del_theme($id);
        break;

}