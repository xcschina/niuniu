<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('product_web');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id", false);
$bo = new product_web();
switch ($act){
    case'info':
        $bo->buy_view($id);
        break;
    case'buy': // 版本切换唯一修改处：
        // v2 <zbc> 
        $type  = paramUtils::intByGET("ch",true);
        $pro_id = paramUtils::intByGET("pid",true);
        $ch_id = paramUtils::intByGET("chid",true);
        $bo->buy_view($id,$type,$pro_id,$ch_id);
        break;
    case'order':
        $bo->order_view($id); // old order
        break;
    case'pay':
        $bo->do_pay($id);
        break;
    case'collection':
        $bo->add_collections($id);
        break;
    case'uncollection':
        $bo->del_collections($id);
        break;

    case'order2':
        $bo->order_view($id); // v2 order
        break;
    case'valid_discount':
        $ch_id = paramUtils::intByGET("ch",true);
        $bo->get_product_valid_discount($id,$ch_id);
        break;
    case'channel_servs':
        $ch_id = paramUtils::intByGET("ch",false);
        $bo->get_product_channel_servs($id, $ch_id);
        break;
    case'topay':
        $bo->do_pay($id);
        break;
    case'pay_result':
        $order_id = paramUtils::strByGET("o");
        $result = paramUtils::strByGET("r",false);
        $bo->ali_pay_return_view($order_id, $result);
        break;
    case'check_firstpay':
        $game_user = paramUtils::strByGET("fpay",false);
        $bo->check_game_user($id,$game_user);
    break;
    case'search_coin':
        $ftype   = paramUtils::strByGET("ftype",false);
        $game_id = paramUtils::intByGET("gid",false);
        $bo->get_coin_products($id,$ftype,$game_id);
        break;
    case'search_count':
        $game_id = $id;
        $bo->get_count_products($game_id);
        break;
    case'wx_order_query':
        $order_id = paramUtils::strByGET("order_id");
        $bo->wx_order_query($order_id);
        break;
// case'game_products':
//     $type = paramUtils::intByGET("type",false);
//     $bo->v2_get_game_type_products($id,$type);
//     break;
}