<?php
COMMON('dao');
class my_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }


    public function get_my_order($user_id,$status='',$page){
        $data = memcache_get($this->mmc, "order_u_".$user_id."_s_".$status.'_p_'.$page);
        if (!$data) {
            $this->limit_sql="select o.*,game.game_name from orders as o left join products as p on o.product_id=p.id left join game on o.game_id = game.id where  o.buyer_id=? ";
            if($status && is_numeric($status) || $status=='0'){
                $this->limit_sql=$this->limit_sql." and o.status=".$status;
            }
            $this->limit_sql.=" order by id desc";
            $this->params=array($user_id);
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc, "order_u_".$user_id."_s_".$status.'_p_'.$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_my_gifts($user_id,$page){
        $this->limit_sql="select gm.game_name,cd.* from game_gifts cd inner join game gm on cd.game_id=gm.id where  cd.buyer_id=? and cd.is_use=1 ORDER BY cd.buy_time desc ";
        $this->params=array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }
}