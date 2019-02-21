<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/5 >
// --------------------------------------
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set('display_errors', 'off');
COMMON("paramUtils","loginCheck");
BO('shop_info_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id");

$bo = new shop_info_web();
switch ($act){

    // -----------------------
    // 店铺列表
    // -----------------------

    // 店铺列表页
    case 'shop_list_view':  
    $bo->shop_list_view();
    break;

    // 店铺新增
    case 'shop_add_view':
    $bo->shop_add_view();
    break;

    case 'shop_add_do':
    $bo->shop_add_do();
    break;

    // 店铺编辑
    case 'shop_edit_view':
    $bo->shop_edit_view($id);
    break;

    case 'shop_edit_do':
    $bo->shop_edit_do();
    break;

    // 店铺列表页_锁定/解锁店铺
    case 'shop_lock_do':
    $bo->shop_lock_do($id,1);
    break;

    case 'shop_unlock_do':
    $bo->shop_lock_do($id);
    break;


    // -----------------------
    // 我的店铺
    // -----------------------

    // 店铺信息页
    case 'shop_info_view':
    $bo->shop_info_view();
    break;


    // -----------------------
    // 我的游戏
    // -----------------------

    // 店铺游戏列表页
    case 'shop_game_list_view':
    $bo->shop_game_list_view($id);
    break;

    // 店铺游戏新增 
    case 'shop_game_add_view':
    $bo->shop_game_add_view($id);
    break;

    case 'shop_game_add_do':
    $bo->shop_game_add_do();
    break;

    // 店铺游戏编辑
    case 'shop_game_edit_view':
    $bo->shop_game_edit_view($id);
    break;

    case 'shop_game_edit_do':
    $bo->shop_game_edit_do();
    break;

    // 店铺游戏屏蔽渠道编辑
    case 'shop_game_ch_edit_view':
    $bo->shop_game_ch_edit_view($id);
    break;

    case 'shop_game_ch_edit_do':
    $bo->shop_game_ch_edit_do();
    break;

    // 店铺商品列表
    case 'shop_product_list_view':
    $shop_id = paramUtils::intByGET("shop_id");
    $bo->shop_product_list_view($id, $shop_id);
    break;

    // 店铺商品编辑
    case 'shop_product_ch_edit_view':
    $shop_id = paramUtils::intByGET("shop_id");
    $bo->shop_product_ch_edit_view($id, $shop_id);
    break;

    case 'shop_product_ch_edit_do':
    $bo->shop_product_ch_edit_do();
    break;


    // -----------------------
    // 我的订单
    // -----------------------
    case 'shop_order_list_view':
    $shop_id = $id;
    $bo->shop_order_list_view($shop_id);
    break;

    case 'shop_order_list_export_do':
    $shop_id = $id;
    $bo->shop_order_list_export_do($shop_id);
    break;


    default:break;
}