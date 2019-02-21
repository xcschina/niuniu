<?php
COMMON('dao','randomUtils');
class activity_info_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_activity_info($id){
        $this->sql = "select a.*,g.game_name,g.product_img from activity_tb as a left join game as g on a.game_id=g.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result ;
    }

    public function get_coupon_info($coupon_id,$user_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id = ? and user_id = ? and receive_time is NOT NULL";
        $this->params = array($coupon_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_receive_id($user_id,$activity_id){
        $this->sql = "select * from activity_log_tb where user_id = ? and activity_id = ?";
        $this->params = array($user_id,$activity_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_activity_info($user_id,$active_id){
        $this->sql = "insert into activity_log_tb(user_id,activity_id,add_time) VALUES(?,?,?)";
        $this->params = array($user_id,$active_id,time());
        $this->doInsert();
    }

    public function get_coupon_last_log($coupon_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id =? and receive_time is NULL";
        $this->params = array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_type_coupon($value){
        $this->sql = "select * from coupon_tb where id = ?";
        $this->params = array($value);
        $this->doResult();
        return $this->result;
    }

    public function update_coupon_log_date($id,$endtime,$user_id){
        $this->sql = "update coupon_user_log_tb set user_id=?,receive_time=?,start_time=?,end_time=? where id = ? ";
        $this->params = array($user_id,time(),time(),$endtime,$id);
        $this->doExecute();
        return $this->result;
    }

    public function update_coupon_log($id,$start_time,$end_time,$user_id){
        $this->sql = "update coupon_user_log_tb set user_id=?,receive_time=?,start_time=?,end_time=? where id = ? ";
        $this->params = array($user_id,time(),$start_time,$end_time,$id);
        $this->doExecute();
        return $this->result;
    }

    public function get_gift($batch_id){
        $this->sql = "select a.code,a.id,g.id as bacth_id,g.content from game_gifts as a left join game_gift_info as g on a.batch_id=g.id  where a.is_use=0 and a.batch_id=?";
        $this->params = array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_code_status($gift,$activity_id, $user_id, $batch_id){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params = array($user_id, strtotime("now"), $gift['id']);
        $this->doExecute();

        $this->sql = "update game_gift_info set remain=remain-1 where id=?";
        $this->params = array($batch_id);
        $this->doExecute();

        $this->sql = "insert into activity_log_tb(user_id,activity_id,add_time) values(?,?,?)";
        $this->params = array($user_id,$activity_id,time());
        $this->doExecute();
        memcache_delete($this->mmc, 'usr_activity'.$user_id);
    }

    public function get_gift_remin($gift){
        $this->sql = "select * from game_gift_info where id = ?";
        $this->params = array($gift);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_num($coupon_id){
        $this->sql = "select count(*) as num from coupon_user_log_tb where coupon_id =? and receive_time is NULL";
        $this->params = array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function update_remain($params,$gift_remain,$full_remain,$coupon_remain){
        $this->sql = "update activity_tb set gift_remain=?,full_remain=?,coupon_remain=? where id=?";
        $this->params = array(implode(",",$gift_remain),implode(",",$full_remain),implode(",",$coupon_remain),$params["activity_id"]);
        $this->doExecute();
    }

}
?>
