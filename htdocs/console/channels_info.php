<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('channels_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new channels_info_web();
    switch ($act){
        case"channels_list":
            $bo->get_channels_list();
            break;
        case"channel_add_view":
            $bo->channel_add_view();
            break;
        case"do_channel_add":
            $bo->add_channel();
            break;
        case"channel_edit_view":
            $bo->channel_edit_view($id);
            break;
        case"do_channel_edit":
            $bo->edit_channel();
            break;
        default:
            $bo->get_channels_list();
            break;
}