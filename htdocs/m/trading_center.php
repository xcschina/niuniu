<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","Off");
BO("trading_mobile");
COMMON('paramUtils');
$bo  = new trading_mobile();
$act = paramUtils::strByGET("act", false);
switch($act){
    case'account_center':
        $bo->account_center();
        break;
    case'order_list':
        $status = paramUtils::strByGET('status');
        $bo->order_list($status);
        break;
    case'get_order':
        $status = paramUtils::strByGET('status', false);
        $bo->get_order($status);
        break;
    case'order_detail':
        $id = paramUtils::strByGET('id', false);
        $bo->order_detail($id);
        break;
    case'collect_list':
        $bo->collect_list();
        break;
    case'clear_invalid':
        $bo->clear_invalid();
        break;
    case'del_collect':
        $bo->del_collect();
        break;
    case'consume_record':
        $bo->consume_record();
        break;
    case'real_verify':
        $bo->real_name_verify();
        break;
    case'do_real_verify':
        $bo->do_real_verify();
        break;
    case'phone_bind':
        $bo->phone_bind();
        break;
    case'do_phone_bind':
        $bo->do_phone_bind();
        break;
    case'sms_code':
        $bo->sms_code();
        break;
    case'message_list':
        $bo->message_list();
        break;
    case'message_detail':
        $id = paramUtils::intByGET('id', false);
        $bo->message_detail($id);
        break;
    case'confirm_view':
        $goods_id = paramUtils::intByGET('goods_id', false);
        $bo->confirm_view($goods_id);
        break;
    case'agreement_view':
        $bo->agreement_view();
        break;
    case'close_trade':
        $bo->close_trade();
        break;
    case'confirm_trade':
//        $id = paramUtils::intByGET('id',false);
        $bo->confirm_trade();
        break;
    case'continue_pay':
        $id = paramUtils::intByGET('id', false);
        $bo->continue_pay($id);
        break;
    case'do_continue_pay':
        $bo->do_continue_pay();
        break;
    case'trading_guide':
        $bo->trading_guide();
        break;
    case'guide_detail':
        $id = paramUtils::intByGET('id', false);
        $bo->guide_detail($id);
        break;
    case'confirm_pay':
        $bo->confirm_pay();
        break;
    case'pay_success':
        $bo->pay_success();
        break;
    case 'get_sell_order':
        $status = paramUtils::strByGET('status');
        $bo->get_sell_order($status);
        break;
    case 'seller_delivery':
        $bo->seller_delivery();
        break;
    case 'user_recharge':
        $bo->user_recharge();
        break;
    case 'already_certification':
        $user_id = paramUtils::intByGET('user_id', false);
        $bo->already_certification($user_id);
        break;
    case 'user_certification':
        $bo->user_certification();
        break;
    case 'already_security_mobile':
        $user_id = paramUtils::intByGET('user_id', false);
        $bo->already_security_mobile($user_id);
        break;
    case 'security_mobile':
        $bo->security_mobile();
        break;
    case 'pay_passward':
        $bo->pay_passward();
        break;
    case 're_pay_passward':
        $bo->re_pay_passward();
        break;
    case 'pay_account':
        $bo->pay_account();
        break;
    case 'already_pay_account':
        $bo->already_pay_account();
        break;
    case 'get_sell_product':
        $status = paramUtils::strByGET('status');
        $bo->get_sell_product($status);
        break;
    case 'sell_product_detail':
        $product_id = paramUtils::strByGET('id');
        $bo->sell_product_detail($product_id);
        break;
    case 'get_my_order_list':
        $bo->get_my_order_list();
        break;
    case 'get_sell_order_data':
        $bo->get_sell_order_data();
        break;
    case 'recharge_ali_pay_return':
        $bo->recharge_ali_pay_return();
        break;
    case 'recharge':
        $bo->recharge();
        break;
    case 'recharge_detail':
        $bo->recharge_detail();
        break;
    case 'get_sell_product_data':
        $bo->get_sell_product_data();
        break;

}