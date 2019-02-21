<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('game_gifts_web');
$act = paramUtils::strByGET("act", false);

$bo = new game_gifts_web();
switch ($act){
    //礼包批次
    case"gift_info_list":
        $bo->gift_info_list();
        break;
    case"gift_info_view":
        $bo->gift_info_view();
        break;
    case"do_edit":
        $bo->do_edit();
        break;
    case"do_save":
        $bo->do_save();
        break;
    //手游礼包
    case"gifts_list":
        $bo->get_gifts_list();
        break;
    case"add_view":
        $bo->add_view();
        break;
    case"import_view":
        $bo->import_view();
        break;
    case"booking_import_view":
        $bo->booking_import_view();
        break;
    case"do_booking_import":
        $bo->do_booking_import();
        break;
    case"do_import":
        $bo->do_import();
        break;
    case"do_del";
        $id = paramUtils::intByGET("id");
        $bo->do_del($id);
        break;
}