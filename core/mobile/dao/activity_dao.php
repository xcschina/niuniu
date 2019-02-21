<?php
COMMON('dao','randomUtils');
class activity_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //查看礼包是否使用
    public function get_my_gift($user_id,$batch_id){
        $this->sql = "select * from game_gifts where buyer_id = ? and batch_id = ?";
        $this->params=array($user_id,$batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_my_gift_rod($batch_id){
        $this->sql = "select * from game_gifts where buyer_id = 0 and batch_id = ?";
        $this->params=array($batch_id);
        $this->doResult();
        return $this->result;
    }


    //查看礼包是否领取完
    public function query_last_gift($batch_id){
        $this->sql = "select * from game_gifts where batch_id = ? and is_use = 0";
        $this->params=array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_gift($id){
        $this->sql = "select * from game_gifts where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_game_id($id){
        $this->sql = "select game_name from game where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    //领取礼包
    public function update_game_gifts($id,$user_id){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params=array($user_id,time(),$id);
        $this->doExecute();
    }

    //更新剩余数量
    public function update_game_info_remain($id){
        $this->sql = "update game_gift_info set remain=remain-1 where id=?";
        $this->params=array($id);
        $this->doExecute();
    }

    public function get_coupon_isnot($coupon_id){
        $this->sql = "select * from coupon_user_log_tb where user_id=0 and coupon_id=?";
        $this->params=array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_isdraw($user_id,$coupon_id){
        $this->sql = "select * from coupon_user_log_tb where user_id=? and coupon_id=?";
        $this->params=array($user_id,$coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_last_log($coupon_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id = ? and receive_time is NULL";
        $this->params=array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_time($id){
        $this->sql = "select * from coupon_tb where id = ?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_coupon_log_date($id,$endtime,$user_id,$start_time,$end_time){
        if(!empty($endtime)){
            $this->sql = "update coupon_user_log_tb set user_id=?,receive_time=?,start_time=?,end_time=? where id = ? ";
            $this->params=array($user_id,time(),time(),$endtime,$id);
        }else{
            $this->sql = "update coupon_user_log_tb set user_id=?,receive_time=?,start_time=?,end_time=? where id = ? ";
            $this->params=array($user_id,time(),$start_time,$end_time,$id);
        }
        $this->doExecute();
        return $this->result;
    }

    public function get_type_coupon($value){
        $this->sql = "select * from coupon_tb where id = ?";
        $this->params=array($value);
        $this->doResult();
        return $this->result;
    }

    public function get_share_log($user_id){
        $this->sql = "select * from share_log where user_id = ?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }


    public function insert_share_log($user_id){
        $this->sql = "insert into share_log(user_id,award_num) VALUES(?,?)";
        $this->params=array($user_id,0);
        $this->doInsert();
    }

    public function update_share_log($new_num,$user_id){
        $this->sql = "update share_log set award_num = ? where user_id = ? ";
        $this->params=array($new_num,$user_id);
        $this->doExecute();
    }

    public function update_share_isuse($user_id){
        $this->sql = "update share_log set is_share = 1,award_num = 1 where user_id = ? ";
        $this->params=array($user_id);
        $this->doExecute();
    }

    public function count_share_log(){
        $this->sql="select count(*) as num from share_log";
        $this->params=array();
        $this->doResult();
        return $this->result;
    }

}