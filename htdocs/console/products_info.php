<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'off');
COMMON("paramUtils","loginCheck");

BO('products_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new products_info_web();
    switch ($act){
        case"products_list":
            $bo->get_products_list();
            break;
        case"products_add_view":
            $bo->products_add_view();
            break;
        case"do_products_add":
            $bo->do_products_add();
            break;
        case"products_edit_view":
            $bo->products_edit_view($id);
            break;
        case"do_products_edit":
            $bo->do_products_edit();
            break;
        case"import_view":
            $bo->import_view();
            break;
        case"do_import":
            $bo->do_import();
            break;
        case"products_ch_view":
            $bo->product_ch_view($id);
            break;
        case 'do_save_ch':
            $bo->do_save_ch();
            break;
        case'special_products_list':
            $bo->get_special_products_list();
            break;
        case'special_products_add_view':
            $bo->special_products_add_view();
            break;
        case'products_bygame_id':
            $bo->get_products_bygame_id();
            break;
        case'do_special_products_save':
            $bo->do_special_products_save();
            break;
        case'special_products_edit_view':
            $bo->special_products_edit_view($id);
            break;
        case'do_special_products_edit':
            $bo->do_special_products_edit();
            break;
        case'product_batch_pub':
            $bo->product_batch_pub();
            break;
        case'product_batch_unpub':
            $bo->product_batch_unpub();
            break;
        case'intro_img_list':
            $bo->intro_img_list($id);
            break;
        case'intro_img_add_view':
            $bo->intro_img_add_view();
            break;
        case'do_intro_img_add':
            $bo->do_intro_img_add();
            break;
        case'del_intro_img':
            $bo->del_intro_img($id);
            break;
        case'game_ch_servs':
            $game_id = paramUtils::intByGET("game_id", false);
            $ch_id = paramUtils::intByGET("ch_id", false);
            $bo->game_ch_servs($game_id, $ch_id);
            break;
        case"products_del_view":
            $id = paramUtils::intByGET('id',false);
            $bo->products_del_view($id);
            break;
        case"do_del":
            $bo->do_del();
            break;
        case"refuse":
            $bo->refuse($id);
            break;
        case'do_refuse':
            $bo->do_refuse();
            break;
        case"products_audit":
            $bo->products_audit($id);
            break;
        case'do_products_audit':
            $bo->do_products_audit();
            break;
        default:
            $bo->get_products_list();
            break;
}