<?php
// --------------------------------------
//  店铺系统 - 公用 <zbc> < 2016/4/14 >
// --------------------------------------
COMMON('dao','randomUtils');
class common_shop_dao extends Dao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_channels(){
        $this->sql = "select * from channels";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    // 主站游戏渠道折扣
    public function get_game_ch_discount($game_id){
        $data = memcache_get($this->mmc, 'game_ch_discount'.$game_id);
        if(!$data){
            $this->sql = 'select * from channel_discount where game_id=?';
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'game_ch_discount'.$game_id, $data, 1, 60);
        }
        return $data;
    }

    // 主站商品折扣[上架与否不重要]
    public function get_product_discount($product_id){
        $this->sql="select p.*,g.game_name,g.tags,g.product_img,pd.* from products p
        inner join game g on p.game_id=g.id left join product_discounts as pd on p.id=pd.product_id where p.id=?"; 
        $this->params=array($product_id);
        $this->doResult();
        return $this->result;
    }
}
