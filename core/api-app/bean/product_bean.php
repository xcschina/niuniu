<?php
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
    public $status;
    public $buy_time;
    public $pay_channel;
    public $qq;
    public $tel;
    public $discount;
    public $discount_in;
    public $role_name;
    public $role_back_name;
    public $service_id;
    public $game_user;
    public $game_pwd;
    public $platform;
    public $is_rand_user=0;
    public $attr;
    public $is_agent;
    public $role_level=0;
    public $reduce_product = 1;

    public function __construct() {
    }
}