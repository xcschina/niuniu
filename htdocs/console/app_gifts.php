<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('app_gifts_web');
$act = paramUtils::strByGET("act", false);
$bo = new app_gifts_web();
switch ($act){
    case"gift_list":
        $bo->gift_list();
        break;
    case"gift_view":
        $bo->gift_view();
        break;
    case"do_edit":
        $bo->do_edit();
        break;
    case"do_save":
        $bo->do_save();
        break;
    case"gifts_list":
        $bo->gifts_list();
        break;
    case"add_view":
        $bo->add_view();
        break;
    case"import_view":
        $bo->import_view();
        break;
    case"do_import":
        $bo->do_import();
        break;
    case"do_del";
        $id = paramUtils::intByGET("id");
        $bo->do_del($id);
        break;
}