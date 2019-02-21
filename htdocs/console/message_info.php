<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('message_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new message_info_web();
    switch ($act){
        case"massages_list":
            $bo->get_massages_list();
            break;
        case"massages_add_view":
            $bo->massages_add_view();
            break;
        case"do_massages_add":
            $bo->do_massages_add();
            break;
}