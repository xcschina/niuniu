<?php
// -------------------------------------------
//  店铺系统 - 商品 <zbc> < 2016/4/14 >
// -------------------------------------------
BO('m'.DS.'common_shop');
DAO('m'.DS.'product_shop_dao');
DAO('m'.DS.'index_shop_dao');
DAO('m'.DS.'game_shop_dao');
DAO('m'.DS.'order_shop_dao');

class product_shop extends common_shop {

    protected $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new product_shop_dao();
    }

    public function shop_product_buy_character_view($params=array()){
        $shop_id = $this->shop_check_id($params['shop_id'],'店铺不存在哟~~~');
        $game_id = $this->shop_check_id($params['game_id'],'游戏不存在哟~~~');
        $this->shop_check_exist($shop_id);
        $this->shop_game_check_exist($shop_id,$game_id);
        if((int)$params['product_id']){
            $this->shop_product_check_exist($shop_id,$game_id,(int)$params['product_id']);
        }
        $this->set_session_back_url();
        $index_shop_dao = new index_shop_dao();
        $game_shop_dao  = new game_shop_dao();
        $shop = $index_shop_dao->get_shop_info($shop_id); 
        $shop_game = $game_shop_dao->get_shop_game_info($params, 1);

        if($params['product_id']){
            $product_id = $params['product_id']; // 指定商品
        }else{
            $params['type'] = 1;
            $products = $this->DAO->get_master_game_products($params);
            // 未指定，取首个商品
            $product_id       = $products[0]['id'];
            $product_price    = $products[0]['price'];
            $product['id']    = $products[0]['id'];
            $product['title'] = $products[0]['title'];
            $product['price'] = $products[0]['price'];
        }
        if(!$product_price){
            $master_product_info = $this->DAO->get_master_product($product_id);
            $product['id']    = $product_id;
            $product['title'] = $master_product_info['title'];
            $product['price'] = $master_product_info['price']?:1000000;
        }

        $chs = $this->shop_product_final_ch_discounts($shop_id, $game_id, $product_id);
        $this->set_product_tag($shop_game['tags']);

        $this->page_hash();
        $this->assign('shop', $shop);
        $this->assign('shop_game', $shop_game);
        $this->assign('product', $product);
        $this->assign('chs', $chs);
        $this->display(self::TPL.'/shop_product_buy_character.html');
    }

    public function shop_product_buy_recharge_view($params=array()){
        $shop_id = $this->shop_check_id($params['shop_id'],'店铺不存在哟~~~');
        $game_id = $this->shop_check_id($params['game_id'],'游戏不存在哟~~~');
        $this->shop_check_exist($shop_id);
        $this->shop_game_check_exist($shop_id,$game_id);
        if((int)$params['product_id']){
            $this->shop_product_check_exist($shop_id,$game_id,(int)$params['product_id']);
        }
        $this->set_session_back_url();
        $index_shop_dao = new index_shop_dao();
        $game_shop_dao  = new game_shop_dao();
        $shop = $index_shop_dao->get_shop_info($shop_id); 
        $shop_game = $game_shop_dao->get_shop_game_info($params, 1);

        if($_SESSION['user_id'] && !$params['orderid']){
            $params['user_id'] = $_SESSION['user_id'];
            $params['shop_id'] = $shop_id;
            $master_user_characters = $game_shop_dao->get_master_user_characters($params);
            $this->assign('characters', $master_user_characters);
        }

        // 指定首充号信息 可以不登录
        // 当前用户 或 验证通过的主站其他用户 的指定首充号 信息
        if($params['orderid']){
            // 根据order.id查询已发货的首充订单信息
            $order_shop_dao = new order_shop_dao();
            $params['id'] = $params['orderid'];
            $order = $order_shop_dao->shop_order_info($params,1,0);
            if($order){
                if(!in_array($order['shop_id'], array('0',$shop_id))){
                    $this->assign('status','not_this_shop_sale');
                }
                $this->assign('order',$order);
            }else{
                $this->assign('status','order_not_found');
            }
        }

        if($params['product_id']){
            $product_id = $params['product_id']; // 指定商品
        }else{
            $params['type'] = 2;
            $products = $this->DAO->get_master_game_products($params);
            $product_id       = $products[0]['id']; // 未指定，取首个商品
            $product_price    = $products[0]['price'];
            $product['id']    = $products[0]['id'];
            $product['title'] = $products[0]['title'];
            $product['price'] = $products[0]['price'];
        }
        $chs = $this->shop_product_final_ch_discounts($shop_id, $game_id, $product_id);
        foreach ($chs as $v1) {
            if(is_array($v1)){
                foreach ($v1 as $v2) {
                    if($v2['id'] == $order['game_channel']){
                        $ch_info = $v2;
                        break;
                    }
                }
            }else{
                if($v1['id'] == $order['game_channel']){
                    $ch_info = $v1;
                }
            }
        }
        // 首充号订单的渠道过滤
        if($ch_info){
            $ch['id']       = $ch_info['id'];
            $ch['name']     = $ch_info['channel_name'];
            $ch['icon']     = $ch_info['icon'];
            $ch['platform'] = $ch_info['platform'];
            $ch['priority_discount'] = $ch_info['priority_discount'];
        }else{
            $ch['id']       = $order['game_channel'];
            $ch['name']     = $order['channel_name'];
            $ch['icon']     = $order['channel_icon'];
            $ch['platform'] = $order['channel_platform'];
            $ch['priority_discount'] = 10;
        }
        if(!$product_price){
            $master_product_info = $this->DAO->get_master_product($product_id);
            $product['id']    = $product_id;
            $product['title'] = $master_product_info['title'];
            $product['price'] = $master_product_info['price']?:1000000;
        }
        $product['dis_price'] = round($ch['priority_discount']*$product['price']/10);
         
        $this->page_hash();
        $this->assign('ch', $ch);
        $this->assign('shop', $shop);
        $this->assign('product', $product);
        $this->assign('shop_game', $shop_game);
        $this->display(self::TPL.'/shop_product_buy_recharge.html');
    }

    // 游戏职业
    protected function set_product_tag($tags){
        $atts = array();
        if(!$tags){
            $this->assign("tags",array());
            return false;
        }
        $tags = explode("\n", $tags);
        if(!is_array($tags))return false;
        foreach($tags as $k=>$v){
            if($v){
                $tag = explode("：",$v);
                $atts[$tag[0]] = explode("|",$tag[1]);
            }
        }
        $this->assign("tags", $atts);
    }
}