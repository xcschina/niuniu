<?php
COMMON('dao');
class le8_pay_dao extends Dao {

	public function __construct() {
		parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
	}

	public function get_le8_user_info($user_id){
        memcache_delete($this->mmc,'l8_user_info'.$user_id);
        $data = memcache_get($this->mmc,'l8_user_info'.$user_id);
        if(empty($data)){
            $this->sql = "select * from user_info where le8_user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'l8_user_info'.$user_id, $data, 1, 60);
        }
        return $data;
    }

    public function get_user_info($user_id){
        memcache_delete('user_info'.$user_id);
        $data = memcache_get($this->mmc,'user_info'.$user_id);
        if(empty($data)){
            $this->sql = "select * from user_info where user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'user_info'.$user_id, $data, 1, 60);
        }
        return $data;
    }

    public function get_user_info_byname($user_name){
        $data = memcache_get($this->mmc,'user_info'.$user_name);
        if(empty($data)){
            $this->sql = "select * from user_info where user_id=?";
            $this->params = array($user_name);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'user_info'.$user_name, $data, 1, 60);
        }
        return $data;
    }

    public function update_l8_user($nn_user_id, $l8_user_id){
        $this->sql = "update user_info set le8_user_id=? where user_id=?";
        $this->params = array($l8_user_id, $nn_user_id);
        $this->doExecute();
        $this->mmc->delete('l8_user_info'.$l8_user_id);
        $this->mmc->delete('user_info'.$nn_user_id);
    }

    public function register_user($user){
//        $this->sql = "insert into user_info(guid, reg_ip, reg_time, login_type, reg_from, token, nick_name, user_name, channel, le8_user_id, login_name)
//          values(?,?,?,?,?,?,?,?,?,?,?)";
//        $this->params = array_values($user);
//        $this->doInsert();
//        $id = $this->LAST_INSERT_ID;
//        return $id;
        return false;
    }

    public function update_user_login_name($login_name, $user_id){
//        $this->sql = "update user_info set login_name=? where user_id=?";
//        $this->params = array($login_name, $user_id);
//        $this->doExecute();
    }

    public function get_trade_order($trade_order_id){
        $this->sql = "select * from le8_orders where le8_order_id=?";
        $this->params = array($trade_order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($order_id){
        $this->sql = "select * from le8_orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_le8_order($le8_order_id, $order_id, $nn_user_id, $le8_user_id, $pay_money, $nnb, $title, $le8_app_id){
        $this->sql = "insert into le8_orders(le8_order_id,order_id,nn_user_id,le8_user_id,pay_money,channel_pay_money,nnb,title,le8_app_id,add_time)values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($le8_order_id, $order_id, $nn_user_id, $le8_user_id, $pay_money, $pay_money, $nnb, $title, $le8_app_id, strtotime("now"));
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    //牛币支付订单
    public function pay_order_nnb($order, $nnb, $channel_pay_money, $status){
        $this->sql = "update le8_orders set nnb=?,channel_pay_money=?,nnb_status=?,status=? where id=?";
        $this->params = array($nnb, $channel_pay_money, 1, 2,$order['id']);
        $this->doExecute();

        $this->sql = "update user_info set nnb=nnb-? where user_id=?";
        $this->params = array($nnb, $order['nn_user_id']);
        $this->doExecute();

        $this->user_lock_nnb(0, $order['nn_user_id']);
        memcache_delete($this->mmc,'user_info'.$order['nn_user_id']);
    }

    public function pay_order_channel($order, $channel_pay_money){
        $this->sql = "update le8_orders set nnb=?,channel_pay_money=? where id=?";
        $this->params = array(0, $channel_pay_money,$order['id']);
        $this->doExecute();
        memcache_delete($this->mmc,'user_info'.$order['nn_user_id']);
    }

    //牛币锁定
    public function user_lock_nnb($status, $user_id){
        $this->sql = "update user_info set nnb_lock=? where user_id=?";
        $this->params = array($status, $user_id);
        $this->doExecute();
    }

    //更新订单状态
    public function update_order_status($status, $id){
        $this->sql = "update le8_orders set status=? where id=?";
        $this->params = array($status, $id);
        $this->doExecute();
    }

    //获取用户
    public function get_nn_user($mobile){
        $data = memcache_get($this->mmc,'nn_user_info'.$mobile);
        if(empty($data)){
            $this->sql = "select * from user_info where m_verified=1 and mobile=?";
            $this->params = array($mobile);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'nn_user_info'.$mobile, $data, 1, 600);
        }
        return $data;
    }

    //已付款订单
    public function get_payed_orders(){
        $this->sql = "select * from le8_orders where status=1 order by le8_callback_time asc";
        $this->doResultList();
        return $this->result;
    }

    //结束订单
    public function finish_order($id, $status){
        $this->sql = "update le8_orders set status=?,le8_callback_time=? where id=?";
        $this->params = array($status, strtotime("now"), $id);
        $this->doExecute();
    }

    //牛币日志
    public function nnb_log($guid, $user_id, $amount, $order_id, $ip){
        $this->sql = "insert into nnb_log(guid,user_id,do,amount,order_id,`from`,ip,add_time)values(?,?,?,?,?,?,?,?)";
        $this->params = array($guid, $user_id, 2, $amount, $order_id, 2, $ip, strtotime("now"));
        $this->doInsert();
    }
}
?>