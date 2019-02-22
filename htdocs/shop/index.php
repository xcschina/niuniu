<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/14 >
// --------------------------------------

header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index');

$bo = new index();
$act = paramUtils::strByGET("act");
$id = paramUtils::intByGET("id");

switch ($act) {
    case 'shop_list_view':
        $bo->redirect('index_shop','shop_list_view'); 
        break;

    case 'shop':
        $params['shop_id'] = $id;
        $bo->redirect('index_shop','shop_info_view',$params); 
        break;

    case 'shop_game':
        $params['shop_id'] = $id;
        $params['game_id'] = paramUtils::intByGET("gid");
        $bo->redirect('game_shop','shop_game_info_view',$params);
        break;

    case 'shop_game_product':
        $params['shop_id']    = $id;
        $params['game_id']    = paramUtils::intByGET("gid");
        $params['orderid']    = paramUtils::intByGET("oid"); // 首充号
        $params['product_id'] = paramUtils::intByGET("pid"); // 商品
        $type = paramUtils::intByGET("type");
        if($type == 1){
            $method = 'shop_product_buy_character_view';
        }elseif($type == 2){
            $method = 'shop_product_buy_recharge_view';
        }
        $bo->redirect('product_shop', $method, $params);
        break;

    case 'shop_order_review_do':
        $params = $_POST;
        $bo->redirect('order_shop', 'shop_order_review_do', $params);
        break;
        
    case 'shop_order_create_do':
        $params = $_POST;
        $bo->redirect('order_shop', 'shop_order_create_do', $params);
        break;

    case 'shop_order_pay_view':
        $params['id'] = $id;
        $params['order_id'] = paramUtils::strByGET('order_id');
        $bo->redirect('order_shop', 'shop_order_pay_view', $params);
        break;

    case 'shop_order_pay_do':
        $params['id'] = $id;
        $params['order_id']    = paramUtils::strByGET('order_id');
        $params['pay_channel'] = paramUtils::intByPOST('pay-channel');
        $bo->redirect('order_shop', 'shop_order_pay_do', $params);
        break;

    case 'shop_order_detail_view':
        $params['id'] = $id;
        $params['order_id'] = paramUtils::strByGET('order_id');
        $bo->redirect('order_shop', 'shop_order_detail_view', $params);
        break;

    case 'ajax_more_shops':
        $params['pageNow'] = paramUtils::intByPOST("pageNow");
        $bo->redirect('index_shop', 'shop_list_view', $params);
        break;

    case 'ajax_products':
        $params['shop_id'] = paramUtils::intByGET("shop_id");
        $params['game_id'] = paramUtils::intByGET("game_id");
        $params['type']    = paramUtils::intByGET("buy_type");
        $params['orderid'] = paramUtils::intByGET("order");
        $bo->redirect('ajax_shop', 'ajax_products', $params);
        break;

    case 'ajax_servs':
        $params['shop_id'] = paramUtils::intByGET("shop_id");
        $params['game_id'] = paramUtils::intByGET("game_id");
        $params['ch_id']   = paramUtils::intByGET("ch_id");
        $bo->redirect('ajax_shop', 'ajax_servs', $params);
        break;

    case 'ajax_login':
        $bo->redirect('ajax_shop', 'ajax_login', $params);
        break;

    case 'ajax_shop_search_games':
        $params['keyword'] = paramUtils::strByGET("keyword"); //关键字
        $params['letter']  = paramUtils::strByGET("letter");  //首字母
        $params['shop_id'] = paramUtils::intByGET("shop");
        $bo->redirect('ajax_shop', 'ajax_shop_search_games', $params);
        break;

    case 'ajax_game_ch_serv_characters':
        $params['shop_id'] = paramUtils::intByGET("shop_id");
        $params['game_id'] = paramUtils::intByGET("game_id");
        $params['ch_id']   = paramUtils::intByGET("ch_id");
        $params['serv_id'] = paramUtils::intByGET("serv_id");
        $bo->redirect('ajax_shop', 'ajax_game_ch_serv_characters', $params);
    break;

    case 'user_login_view':
        $bo->redirect('account_shop', 'login', $params);
        break;

    case 'user_login_do':
        $bo->redirect('account_shop', 'do_login', $params);
        break;

    case 'user_qqlogin_view':
        $bo->redirect('account_shop', 'qq_login_view', $params);
        break;

    case 'check_character':
        $params['character'] = paramUtils::strByGET('character');
        $params['id'] = $id;
        $bo->redirect('game_shop', 'check_character', $params);
        break;

    case 'shop_service_view':
        $params['shop_id'] = $id;
        $bo->redirect('index_shop','shop_service_view',$params);
        break;

    default:
        $bo->redirect('index_shop','shop_list_view'); 
        break;
}