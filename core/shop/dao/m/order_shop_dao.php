<?php
// --------------------------------------
// 店铺系统 - 订单 <zbc> < 2016/4/14 >
// --------------------------------------

COMMON('dao');
class order_shop_dao extends Dao {
	
    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function shop_order_info($params=array(), $moreinfo=0, $need_order_id=1){
        $key = 'shop_order_info';
        if($params['id'])       $key .= '_'.$params['id'];
        if($params['order_id']) $key .= '_'.$params['order_id'];
        $data = memcache_get($this->mmc, $key);
        if(!$data){
        	if($moreinfo){
        		$select = 's.s_name, g.game_name, gs.serv_name, c.channel_name, c.icon as channel_icon, c.platform as channel_platform, o.*';
        		$sql = 'select '.$select.' from orders as o 
        		left join game as g on o.game_id=g.id 
        		left join game_servs as gs on gs.id=o.serv_id and o.game_id=gs.game_id 
        		left join channels as c on c.id=o.game_channel 
        		left join shop as s on s.s_id=o.shop_id
        		where 1=1';
        	}else{
        		$sql = 'select * from orders as o where 1=1';
        	}
            // 必须使用订单order_id才可查询
            if($need_order_id){
                $sql .= ' and o.order_id='.$params['order_id'];
            }
        	if(intval($params['id'])){
        		$sql .= ' and o.id='.$params['id'];
        	}
            $this->sql = $sql;
            $this->params = array();
            $this->doResult();
            $data = $this->result;
            $this->mmc->set($key, $data, 1, 600);
        }
        return $data;
    }

    public function shop_insert_order($order){
        $this->sql = "insert into orders(order_id,title,buyer_id,product_id,amount,money,unit_price,pay_money,game_id,
                      serv_id,game_channel,seller_id,qq,tel,discount,role_name, role_back_name,service_id,role_level,is_rand_user,
                      game_user,game_pwd,attr,platform,is_agent,shop_id,buy_time,status,discount_in,reduce_product,
                      pay_channel,product_type,remarks) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();

        //更新客服信息
        $this->sql = "update `admins` set last_service_time=? where id=?";
        $this->params = array(strtotime("now"), $order['service_id']);
        $this->doExecute();
        return $this->LAST_INSERT_ID;
    }

    public function get_service(){
        $this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 AND last_service_time>0 and id <>'114' order by last_service_time limit 1";
        $this->doresult();
        return $this->result;
    }
}