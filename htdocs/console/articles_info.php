<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","off");
COMMON("paramUtils","loginCheck");

BO('articles_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new articles_info_web();
    switch ($act){
        case"parts_list":
            $bo->get_parts_list();
            break;
        case"part_add_view":
            $bo->part_add_view();
            break;
        case"do_part_add":
            $bo->do_part_add();
            break;
        case"part_edit_view":
            $bo->part_edit_view($id);
            break;
        case"do_part_edit":
            $bo->do_part_edit();
            break;
        case"articles_list":
            $bo->get_articles_list();
            break;
        case"articles_add_view":
            $bo->articles_add_view();
            break;
        case"do_articles_add":
            $bo->do_articles_add();
            break;
        case"articles_edit_view":
            $bo->article_edit_view($id);
            break;
        case"do_articles_edit":
            $bo->do_articles_edit();
            break;
        default:
            break;
}