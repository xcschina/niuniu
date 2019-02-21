<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");

BO('site_setting_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new site_setting_web();
switch ($act){
    case"site_setting_list":
        $bo->get_site_setting_list();
        break;
    case"setting_edit_view":
        $bo->setting_edit_view($id);
        break;
    case"do_setting_edit":
        $bo->do_setting_edit();
        break;
    default:
        $bo->get_site_setting_list();
        break;
}