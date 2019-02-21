<?php
COMMON('dao');
class apk_pay_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_app_info($app_id){
        $data = memcache_get($this->mmc, "66apk_app_info");
        if(!$data){
            $this->sql = "SELECT * from `niuniu`.apps where app_id =".$app_id;
            $this->params = array($app_id);
            $this->doResult();
            $data =$this->result;
            memcache_set($this->mmc,"66apk_app_info",$data, 1, 3600);
        }
        return $data;
    }

    public function create_nnb_order($order){
        $this->sql = "insert into `niuniu`.nnb_orders(app_id,order_id,pay_channel,buyer_id,title,pay_money,nnb_num,`rate`,`status`,buy_time,ip,channel,integral,pay_from,reg_channel)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,2)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_qb_order($user_id,$page){
        $data = memcache_get($this->mmc, "66apk_qb_order_".$page);
        if(!$data){
            $this->limit_sql="select title,order_id,pay_money,`status`,buy_time,pay_time,charge_qq,pay_channel from qb_order where reg_channel = 2 and buyer_id=".$user_id ;
            $this->limit_sql.=" order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_qb_order_".$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_nnb_order($user_id,$page){
        $data = memcache_get($this->mmc, "66apk_nnb_order_".$page);
        if(!$data){
            $this->limit_sql="select title,order_id,pay_money,`status`,buy_time,pay_time,pay_channel,nnb_num from `niuniu`.nnb_orders where reg_channel = 2 and buyer_id=".$user_id ;
            $this->limit_sql.=" order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc,"66apk_nnb_order_".$page,$data, 1, 3600);
        }
        return $data;
    }

    public function get_nnb_order_count($user_id){
        $this->sql="select count(*) as num from `niuniu`.nnb_orders where reg_channel = 2 and buyer_id= ? ";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }


    public function get_qb_order_count($user_id){
        $this->sql="select count(*) as num from qb_order where reg_channel = 2 and buyer_id= ? ";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_qq_rate($id) {
        $this->sql = "select * from setting where id = ".$id;
        $this->doResult();
        return $this->result['qq_discount'];
    }

    public function get_service(){
        $this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 AND last_service_time > 0 and id <>'114' order by last_service_time limit 1";
        $this->doResult();
        return $this->result;
    }

    public function insert_order($order){
        $this->sql = "insert into qb_order(title,buyer_id,order_id,amount,unit_price,money,pay_money,pay_channel,buy_remark,tel,charge_qq,service_id,buy_time,reg_channel)
                                    values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order['title'],$order['buyer_id'],$order['order_id'],$order['amount'],$order['unit_price'],$order['money'],$order['pay_money'],$order['pay_mode'],$order['buy_remark'],$order['tel'],$order['charge_qq'],$order['service_id'],time(),2);
        $this->err_log(var_export($this->params,1),'qb_order');
        $this->doExecute();

        //更新客服信息
        $this->sql = "update `admins` set last_service_time=? where id=?";
        $this->params = array(strtotime("now"), $order['service_id']);
        $this->doExecute();
    }

    public function get_integral_info($money){
        $this->sql = "select * from niuniu.nnb_integral where is_del = 0 and money = ?";
        $this->params = array($money);
        $this->doResult();
        return $this->result;
    }
}