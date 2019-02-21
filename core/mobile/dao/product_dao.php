<?php
COMMON('dao');

class product_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->price_type = array(
            0=>array("","不限"),
            1=>array(" and price<30","30元以下"),
            2=>array(" and price<100 and price>=30","30--100元"),
            3=>array(' and price<300 and price>=100',"100-300元"),
            4=>array(" and price>=300","300元以上")
        );
    }

    public function get_channels(){
        $this->sql = "select * from channels";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_channels_discount($game_id){
        $this->sql="select * from channel_discount where game_id=?";
        $this->params=array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function get_game_discount($game_id, $channel_id, $type){
        $this->sql = "select ch_".$channel_id."_".$type." from channel_discount where game_id=?";
        $this->params = array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function get_product_info($product_id){
        $this->sql="select p.*,g.game_name,g.tags,pd.* from products p
                    inner join game g on p.game_id=g.id left join product_discounts as pd on p.id=pd.product_id where p.id=?";
        $this->params=array($product_id);
        $this->doResult();
        return $this->result;
    }

    public function check_usr_order($user_id, $order_id){
        $this->sql = "select * from orders where buyer_id=? and id=? and status=2";
        $this->params = array($user_id, $order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_game_attach($game_id){
        $this->sql = "SELECT a.*,b.name as tag_name FROM `game_attachs` as a INNER JOIN tags as b on a.tag_id=b.id where a.game_id=?";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_servs($game_id){
        $data = memcache_get($this->mmc,'game_servs'.$game_id);
        if(!$data){
            $this->sql = "SELECT * FROM game_servs WHERE game_id=? order by id desc";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_servs'.$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_serv_info($serv_id){
        $info = memcache_get($this->mmc, 'serv-info'.$serv_id);
        if(!$info){
            $this->sql = "SELECT * FROM game_servs WHERE id=?";
            $this->params = array($serv_id);
            $this->doResult();
            $info = $this->result;
            memcache_set($this->mmc, 'serv-info'.$serv_id, $info);
        }
        return $info;
    }

    public function get_service(){
        $this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 AND last_service_time>0 and id <>'114' order by last_service_time limit 1";
        $this->doResult();
        return $this->result;
    }

    public function insert_order($order){
        $this->sql = "insert into orders(order_id,title,buyer_id,product_id,amount,money,unit_price,pay_money,game_id,serv_id,
                                    game_channel,seller_id,status,buy_time,pay_channel,qq,tel,discount,discount_in,role_name,
                                    role_back_name,service_id,game_user,game_pwd,platform,is_rand_user,attr,is_agent,role_level,reduce_product,coupon_id,other_ser)
                                    values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->err_log(var_export($this->params,1),'order');
        $this->doExecute();

        $this->sql = "update user_info set buy_mobile=? where user_id=?";
        $this->params = array($order['tel'], $order['buyer_id']);
        $this->doExecute();
        $_SESSION['buy_mobile'] = $order['tel'];

        //非首冲号\续充\代充,需要减库存
        if($order['reduce_product']==1){
            $this->sql = "update products set stock=stock-? where id=?";
            $this->params = array($order['amount'],$order['product_id']);
            $this->doExecute();
        }
        //更新优惠券
        if($order['coupon_id']){
            $this->sql = "update coupon_user_log_tb set use_time=? where id=?";
            $this->params = array(time(),$order['coupon_id']);
            $this->doExecute();
        }
        //更新客服信息
        $this->sql = "update `admins` set last_service_time=? where id=?";
        $this->params = array(strtotime("now"), $order['service_id']);
        $this->doExecute();
    }

    public function get_game_user($game_id, $user_id){
        $this->sql = "select a.id,a.game_user,a.role_name,a.serv_id,c.serv_name from orders as a inner join products as b on a.product_id=b.id
                  inner join game_servs as c on a.serv_id=c.id where a.buyer_id=? and b.type=1 and a.status=2 and a.game_id=?";
        $this->params = array($user_id, $game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_user_agent_game($user_id, $game_id){
        $data = $this->mmc->get("user_agent_games".$user_id."-".$game_id);
        if(!$data){
            $this->sql = "select * from agents where game_id=? and user_id=?";
            $this->params = array($game_id, $user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("user_agent_games".$user_id."-".$game_id, $data, 1,600);
        }
        return $data;
    }

    public function get_iap_serv_info($serv_id){
        $info = memcache_get($this->mmc, 'iap-serv-info'.$serv_id);
        if(!$info){
            $this->sql = "select * from 7881_game_servs where id=?";
            $this->params = array($serv_id);
            $this->doResult();
            $info = $this->result;
            memcache_set($this->mmc, 'iapserv-info'.$serv_id, $info);
        }
        return $info;
    }

    public function get_order_info_by_order_id($order_id){
        $this->sql = "select a.*,b.qq as service_qq from orders as a inner join admins as b on a.service_id=b.id where a.order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_qb_order_info_by_order_id($order_id){
        $this->sql = "select a.*,b.qq as service_qq from qb_order as a inner join admins as b on a.service_id=b.id where a.order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_all_coupon($price,$user_id){
        $this->sql="SELECT coupon.name,coupon.type,coupon.discount_type,coupon.discount,coupon.total_amount,coupon.discount_amount,logs.id as logs_id,logs.start_time,logs.end_time,`type`.*
                    from coupon_tb as coupon left join coupon_apply_type as `type` on coupon.id=type.coupon_id LEFT join coupon_user_log_tb as logs on coupon.id=logs.coupon_id
                    WHERE logs.use_time is NULL and logs.end_time > ? and logs.start_time < ? and coupon.total_amount <= ".$price." and logs.user_id = ?";
        $this->params=array(time(),time(),$user_id);
        $this->doResultList();
        return $this->result;
    }
}