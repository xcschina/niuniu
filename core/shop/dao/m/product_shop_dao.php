<?php
// --------------------------------------
//  店铺系统 - 商品 <zbc> < 2016/4/14 >
// --------------------------------------
COMMON('dao');

class product_shop_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    
    public function get_shop_product_info($params=array(),$moreinfo=0){
        if($moreinfo){
            $this->sql = 'select * from shop_game as sg left join products as p on p.game_id=sg.game_id where 1=1 and sg.s_id=? and sg.game_id=? and p.id=? and sg.sg_status=1 and p.is_pub=1';
        }else{
            $this->sql = 'select * from shop_product where 1=1 and s_id=? and game_id=? and product_id=?';
        }
        $this->params = array($params['shop_id'],  $params['game_id'], $params['product_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_master_product($product_id){
        $this->sql = 'select * from products where 1=1 and id=?';
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    public function get_master_game_products($params=array()){
        $this->sql =  "select * from products where 1=1 and is_pub=1 and game_id=? and `type`=?  order by add_time desc,id desc";
        $this->params = array($params['game_id'],$params['type']);
        $this->doResultList();
        return $this->result;
    }
}