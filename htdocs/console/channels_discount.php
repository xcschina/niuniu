<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('channels_discount_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new channels_discount_web();
switch ($act){
    case"channels_discount_list":
        $bo->get_channels_discount_list();
        break;
    case"channels_discount_add_view":
        $bo->channels_discount_add_view();
        break;
    case"do_channels_discount_add":
        $bo->do_channels_discount_add();
        break;
    case"channels_discount_edit_view":
        $bo->channels_discount_edit_view($id);
        break;
    case"do_channels_discount_edit":
        $bo->do_channels_discount_edit();
        break;
    default:
        $bo->get_channels_discount_list();
        break;
}