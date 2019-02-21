<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'Off');
COMMON("paramUtils","loginCheck");

BO('friendly_link_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new friendly_link_web();
    switch ($act){
        case"get_link_list":
            $bo->get_link_list();
            break;
        case"link_add_view":
            $bo->link_add_view();
            break;
        case"do_link_add":
            $bo->do_link_add();
            break;
        case"link_edit_view":
            $bo->link_edit_view($id);
            break;
        case"do_link_edit":
            $bo->do_link_edit();
            break;
        case"del_link":
            $bo->del_link($id);
            break;
        default:
            $bo->get_link_list();
            break;
}