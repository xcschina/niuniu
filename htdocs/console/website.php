<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","loginCheck");
BO('website_admin');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new website_admin();
switch ($act){
    case 'website_list':
        $bo->list_view();
        break;
    case 'add_view':
        $bo->add_view();
        break;
    case 'do_save':
        $bo->do_save();
        break;
    case "promoter_list":
        $bo->promoter_list();
        break;
    case "promoter_add":
        $bo->promoter_add();
        break;
    case "promoter_save":
        $bo->promoter_save();
        break;
    case"website_info":
        $bo->website_info();
        break;
    case"add_info":
        $bo->add_info();
        break;
    case"add_save":
        $bo->add_save();
        break;
    default:
        $bo->list_view();
        break;
}