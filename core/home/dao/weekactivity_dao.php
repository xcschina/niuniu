<?php
COMMON('dao');

class weekactivity_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_channels(){
        $data = $this->mmc->get("channels");
        if(!$data){
            $this->sql = "select * from channels";
            $this->params = array();
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("channels", $data, 1 ,600);
        }
        return $data;
    }

    public function get_game_last_product($game_id, $type=1){
        $data = $this->mmc->get("game_last_product".$game_id."-".$type);
        if(!$data){
            $this->sql = "select p.*,pd.* from products p
                    inner join product_discounts as pd on p.id=pd.product_id where p.is_pub=1 and p.game_id=? and p.type=? order by price asc limit 1";
            $this->params = array($game_id, $type);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_last_product".$game_id."-".$type, $data, 1 ,600);
        }
        return $data;
    }

    public function get_banks(){
        $data = memcache_get($this->mmc, "1bankcodes");
        if(!$data){
            $this->sql = "select * from bankcodes where seq!=15 and t=1 order by seq";
            $this->params = array();
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "1bankcodes", $data, 1, 3600);
        }
        return $data;
    }

    public function get_game($id){
        $this->sql = "select * from game where status=1 and is_del=0 and id=?";
        $this->params = array($id);
        $this->doResult();
        $games = $this->result;
        return $games;
    }

    public function get_game_serv($id){
        $this->sql = "select * from game_servs where game_id =?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function get_article_info($id){
        $data = memcache_get($this->mmc,'wx_artilce_info'.$id);
        if(!$data){
            $this->sql="select * from articles where id=?";
            $this->params=array($id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'wx_artilce_info'.$id, $data, 1, 3600);
        }
        return $data;
    }


    public function get_game_ch_download($gid){
        $data = memcache_get($this->mmc,"game_downs".$gid);
        if(!$data){
            $this->sql = "select a.*,b.icon,b.platform,b.channel_name from game_downloads as a inner join channels as b on a.channel_id=b.id where a.is_del=0 and a.game_id=? order by b.id=6 desc";
            $this->params = array($gid);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"game_downs".$gid, $data,1,600);
        }
        return $data;
    }

    public function get_game_batch(){
        $this->sql="select * from weekactivity_batch where start_time < ".time()." and ".time()." < end_time";
        $this->params = array();
        $this->doResult();
        return $this->result;
    }

    public function get_game_list($id){
        $this->sql="select * from weekactivity where batch_id = ?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function get_weekactivity_tb($id){
        $this->sql = "select * from weekactivity where id =?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_serv_info($serv_id){
        $info = memcache_get($this->mmc, 'serv-info'.$serv_id);
        if(!$info){
            $this->sql = "select * from game_servs where id=?";
            $this->params = array($serv_id);
            $this->doResult();
            $info = $this->result;
            memcache_set($this->mmc, 'serv-info'.$serv_id, $info);
        }
        return $info;
    }

    public function get_service(){
        $this->sql = "select * from `admins` where `group`='vip' and is_del=0 and last_service_time>0 order by last_service_time limit 1";
        $this->doResult();
        return $this->result;
    }

    public function insert_order($order){
        $this->sql = "insert into orders(order_id,title,buyer_id,product_id,amount,money,unit_price,pay_money,game_id,serv_id,
            game_channel,seller_id,status,buy_time,pay_channel,qq,tel,discount,discount_in,role_name,
            role_back_name,service_id,game_user,game_pwd,attr,is_agent,is_rand_user,reduce_product,coupon_id,activity_id)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
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

    public function updata_weekactivity_buynum($num,$id){
        $this->sql="update weekactivity set buy_number=? where id=?";
        $this->params = array($num,$id);
        $this->doExecute();
    }

    public function get_order_status($activity_id,$user_id){
        $this->sql = "select * from orders where activity_id=? and buyer_id = ? and (status=1 or status=2)";
        $this->params = array($activity_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_detail($id,$user_id){
        $this->sql = "select g.game_name,o.*,a.qq as service_qq from orders as o inner join game as g on o.game_id=g.id
                      inner join admins as a on o.service_id=a.id
                      where o.id=? and o.buyer_id=?";
        //$this->sql = "select g.game_name,p.intro,p.type,o.* from orders as o inner join products as p on
        // o.product_id=p.id inner join game as g on o.game_id=g.id where o.id=? and o.buyer_id=?";
        $this->params=array($id,$user_id);
        $this->doResult();
        return $this->result;
    }


}