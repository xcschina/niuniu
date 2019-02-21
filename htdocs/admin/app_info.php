<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");

BO('app_info_admin');
$act = paramUtils::strByGET("act", false);

$bo = new app_info_admin();
switch ($act){
    case"list":
        $bo->info_list();
        break;
    case"add":
        $bo->add_view();
        break;
    case"do_add":
        $bo->do_add();
        break;
    case"edit":
        $id = paramUtils::intByGET("id",false);
        $bo->edit_view($id);
        break;
    case"do_edit":
        $bo->do_edit();
        break;
    case"offline":
        $id = paramUtils::intByGET("id",false);
        $bo->offline_view($id);
        break;
    case"do_offline":
        $bo->do_offline();
        break;
    case"banner_list":
        $bo->banner_list();
        break;
    case"add_banner":
        $bo->add_banner();
        break;
    case"do_add_banner":
        $bo->do_add_banner();
        break;
    case"is_del":
        $id = paramUtils::intByGET("id",false);
        $bo->is_del($id);
        break;
    case"do_del":
        $id = paramUtils::intByGET("id",false);
        $bo->do_del($id);
        break;
}