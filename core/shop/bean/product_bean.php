<?php
// -------------------------------------------------------
// 店铺系统 - 商品订单 <zbc> <2016-04-26>
// -------------------------------------------------------
class productBean {
    public $id;
    public $order_id;
    public $title;
    public $buyer_id;
    public $product_id;
    public $amount;
    public $money;
    public $unit_price;
    public $pay_money;
    public $game_id;
    public $serv_id;
    public $game_channel;
    public $seller_id;
    public $qq;
    public $tel;
    public $discount;
    public $role_name;
    public $role_back_name;
    public $service_id;
    public $role_level=0;
    public $is_rand_user=0;
    public $game_user;
    public $game_pwd;
    public $attr;
    public $platform;
    public $is_agent;
    public $shop_id=0;
    public $buy_time;
    public $status;
    public $discount_in;
    public $reduce_product = 1;
    public $pay_channel;
    public $product_type;
    public $remarks;

    public function __construct() {
    }
}


