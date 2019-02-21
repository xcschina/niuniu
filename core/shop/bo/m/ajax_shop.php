<?php
// -------------------------------------------
// 店铺系统 - 查询 <zbc> <2016-04-26>
// -------------------------------------------
BO('m'.DS.'common_shop');
DAO('m'.DS.'index_shop_dao');
DAO('m'.DS.'order_shop_dao');
DAO('m'.DS.'game_shop_dao');

class ajax_shop extends common_shop {

    public $DAO;
    public function __construct() {
        parent::__construct();
    }

    public function ajax_login(){
        $this->page_hash();
        $this->display("m/ajax/ajax_login.html");
    }

    /**
     * 店铺游戏查询
     * @param letter  首字母
     * @param keyword 关键字
     * @param shop_id
     */
    public function ajax_shop_search_games($params=array()){
        $game_shop_dao = new game_shop_dao();
        if($params['letter']){
            $games = $game_shop_dao->shop_letter_games($params);
        }elseif($params['keyword']){
            $games = $game_shop_dao->shop_keyword_games($params);
        }
        if(count($games)>0){
            $bak['status'] = 1;
            $bak['shop_games']  = $games;
        }else{
            $bak['status'] = 0;
        }
        die(json_encode($bak));
    }
   
    /**
     * 获取店铺游戏的主站所有可选商品
     */
    public function ajax_products($params=array()){
        $product_shop_dao  = new product_shop_dao();
        $products = $product_shop_dao->get_master_game_products($params);
        $this->assign("products", $products);
        $this->assign("params", $params);
        // 续充
        if($params['type']==2){
            $order_shop_dao = new order_shop_dao();
            $params['id'] = $params['orderid'];
            $order = $order_shop_dao->shop_order_info($params,0,0);
            $this->assign("order", $order);
        }
        $this->display("m/ajax/ajax_products.html");
    }

    /**
     * 获取店铺游戏的主站所有可选区服
     */
    public function ajax_servs($params=array()){
        $game_shop_dao  = new game_shop_dao();
        $servs = $game_shop_dao->get_master_game_ch_servs($params);
        $servs = array_chunk($servs, 50);
        $this->assign("servs", $servs);
        $this->display("m/ajax/ajax_servs.html");
    }

    /**
     * 同游戏同渠道同区服已购买过的首充号列表
     */
    public function ajax_game_ch_serv_characters($params=array()){
        $game_shop_dao  = new game_shop_dao();
        $params['user_id'] = $_SESSION['user_id']?:0;
        $characters = $game_shop_dao->get_master_user_characters($params);
        $this->assign('characters',$characters);
        $this->display('m/ajax/ajax_game_ch_serv_characters.html');
    }

}