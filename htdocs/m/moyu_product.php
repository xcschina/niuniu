<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('moyu_product_mobile');

$bo  = new moyu_product_mobile();
$act = paramUtils::strByGET("act");
switch($act){
    case 'get_channel_list':
        $bo->get_channel();
        break;
    case 'get_game_servs_list':
        $bo->get_game_servs_list();
        break;
    case 'get_product_list':
        $id = paramUtils::intByGET("id", false);
        $bo->get_product_list($id);

        break;
    case 'get_product_info':
        $id = paramUtils::intByGET("id", false);
        $bo->get_product_info($id);
        break;
    case 'update_product_collect':
        $id = paramUtils::intByGET("id", false);
        $bo->update_product_collect($id);
        break;
    case 'cancle_product_collect':
        $id = paramUtils::intByGET("id", false);
        $bo->cancle_product_collect($id);
        break;
    case 'get_real_product':
        $bo->get_real_product();
        break;
    case 'get_serv_name':
        $id = paramUtils::intByPOST("id", false);
        $name = paramUtils::strByPOST("serv_name");
        $game_id = paramUtils::strByPOST("game_id", false);
        $bo->get_serv_name($id,$name,$game_id);
        break;
    case 'get_game_list':
        $bo->get_game_list();
        break;
    case 'publish_product':
        $bo->publish_product();
        break;
    case 'update_products_status':
        $bo->update_products_status();
        break;
    case 'get_products_imgs':
        $bo->get_products_imgs();
        break;
    case 'delete_prducts_img':
        $bo->delete_prducts_img();
        break;
    case 'edit_products':
        $bo->edit_products();
        break;
    case 'get_publish_product_info':
        $bo->get_publish_product_info();
        break;
    case 'add_prducts_img':
        $bo->add_prducts_img();
        break;
    case 'publish_product_view':
        $bo->publish_product_view();
        break;
    case 'publish_channel_view':
        $game_id = paramUtils::strByGET("game_id", false);
        $bo->publish_channel_view($game_id);
        break;
    case 'publish_serv_view':
        $id = paramUtils::intByGET("ch_id");
        $name = paramUtils::strByGET("serv_name");
        $game_id = paramUtils::intByGET("game_id");
        $bo->publish_serv_view($id,$name,$game_id);
        break;
    case 'product_category_view':
        $id = paramUtils::intByGET("ch_id", false);
        $serv_id = paramUtils::intByGET("serv_id");
        $game_id = paramUtils::intByGET("game_id", false);
        $bo->product_category_view($id,$serv_id,$game_id);
        break;
    case 'add_product_view':
        $id = paramUtils::intByGET("ch_id", false);
        $serv_id = paramUtils::strByGET("serv_id",false);
        $game_id = paramUtils::strByGET("game_id", false);
        $bo->add_product_view($id,$serv_id,$game_id);
        break;
    case 'pusblish_success':
        $id = paramUtils::intByGET("id", false);
        $bo->pusblish_success($id);
        break;
    case 'deliver_product_view':
        $id = paramUtils::intByGET("id", false);
        $bo->deliver_product_view($id);
        break;
    case"edit_product_view":
        $id = paramUtils::intByGET('id',false);
        $bo->edit_product_view($id);
        break;
    case 'get_user_collect_list':
        $bo->get_user_collect_list();
        break;
    case 'get_user_invalid_collect_list':
        $bo->get_user_invalid_collect_list();
        break;
}