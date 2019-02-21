<?php
COMMON('dao');
class user_dao extends Dao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_user_info($user_id){
        $this->sql = "select * from user_info where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_nnb_log($user_id,$page){
        $this->limit_sql = "select * from nnb_log where user_id=? order by add_time desc";
        $this->params = array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    //牛币锁定
    public function user_lock_nnb($status, $user_id){
        $this->sql = "update user_info set nnb_lock=? where user_id=?";
        $this->params = array($status, $user_id);
        $this->doExecute();
    }

    public function get_qb_order($order_id){
        $this->sql = "select * from qb_order where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function up_qb_order($ali_order_id, $status, $buyer_email,$order_id){
        $this->sql = "update qb_order set channel_order_id=?,status=?,payer=?,pay_time=? where order_id=?";
        $this->params = array($ali_order_id, $status, $buyer_email, time(),$order_id);
        $this->doExecute();
    }

    public function up_ser_qq_info($order){
        $this->sql = "update user_info set is_vip=?,last_buy_time=? where user_id=?";
        $this->params = array(1, strtotime("now"), $order['buyer_id']);
        $this->doExecute();
    }

    //牛币支付订单
    public function add_user_nnb($order){
        $this->sql = "update user_info set nnb=nnb+? where user_id=?";
        $this->params = array($order['nnb_num'], $order['buyer_id']);
        $this->doExecute();

        $this->user_lock_nnb(0, $order['buyer_id']);
        memcache_delete($this->mmc,'user_info'.$order['buyer_id']);
    }

    public function update_user_nnb($order){
        $this->sql = "update user_info set nnb=nnb-? where user_id=?";
        $this->params = array($order['nnb_num'], $order['buyer_id']);
        $this->doExecute();

        $this->user_lock_nnb(0, $order['buyer_id']);
        memcache_delete($this->mmc,'user_info'.$order['buyer_id']);
    }

    //牛币日志
    public function nnb_log($guid,$order,$status){
        $this->sql = "insert into nnb_log(guid,user_id,do,amount,order_id,`from`,ip,add_time)values(?,?,?,?,?,?,?,?)";
        $this->params = array($guid, $order['buyer_id'], $status, $order['nnb_num'], $order['order_id'], 3, $order['ip'], strtotime("now"));
        $this->doInsert();
    }
}