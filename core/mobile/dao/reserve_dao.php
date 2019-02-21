<?php
COMMON('dao','randomUtils');
class reserve_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_draw_params($act_id){
        $data = memcache_get($this->mmc, 'draw_data_'.$act_id);
        if (!$data) {
            $this->sql = "select * from prize_channel  WHERE bespeak_id = ? ";
            $this->params = array($act_id);
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'draw_data_', $act_id, 1, 600);
        }
        return $data;
    }

    public function get_reserve_log($act_id,$user_id){
        $this->sql = "select * from reserve_log  where act_id= ? and user_id= ? order by code desc";
        $this->params = array($act_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_draw_count($code,$act_id){
        $this->sql = "select count(1) as num from reserve_log where code= ? and act_id= ?";
        $this->params = array($code,$act_id);
        $this->doResult();
        return $this->result['num'];
    }

    public function get_draw_log_count($user_id,$act_id){
        $this->sql = "select count(1) as num from reserve_draw_log where user_id= ? and act_id= ? and `do_type`= 1 ";
        $this->params = array($user_id,$act_id);
        $this->doResult();
        return $this->result['num'];
    }

    public function insert_reserve_log($act_id,$user_id,$code,$ip){
        $this->sql = "insert into reserve_log(user_id,act_id,code,add_time,ip) VALUES(?,?,?,?,?)";
        $this->params = array($user_id,$act_id,$code,time(),$ip);
        $this->doInsert();
    }

    public function add_draw_log($user_id,$act_id,$data){
        $this->sql = "insert into reserve_draw_log(user_id,act_id,draw_id,draw_type,draw_desc,prize_id,add_time) VALUES(?,?,?,?,?,?,?)";
        $this->params = array($user_id,$act_id,$data['id'],$data['type'],$data['title'],$data['prize_id'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function add_reserve_draw_log($user_id,$act_id,$gifts_id){
        $this->sql = "insert into reserve_draw_log(user_id,act_id,draw_id,draw_type,draw_desc,prize_id,add_time,do_type) VALUES(?,?,?,?,?,?,?,?)";
        $this->params = array($user_id,$act_id,1,2,'预约礼包',$gifts_id,time(),2);
        $this->doInsert();
    }

    public function update_reserve_log($act_id,$user_id,$code){
        $this->sql = "update reserve_log set code=? where user_id=? and act_id= ?";
        $this->params = array($code,$user_id,$act_id);
        $this->doExecute();
    }

    public function get_act_info($act_id){
        $this->sql = "select * from reserve_act_tb where id = ? ";
        $this->params = array($act_id);
        $this->doResult();
        return $this->result;
    }

    public function get_act_draw_info($act_id){
        $this->sql = "select * from reserve_act_draw where act_id= ?";
        $this->params = array($act_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_reserve_ip_sum($ip){
        $this->sql = "select count(id) as num  from reserve_log where ip= ? ";
        $this->params = array($ip);
        $this->doResult();
        return $this->result;
    }

    public function get_reserve_draw_log($id){
        $this->sql = "select * from reserve_draw_log where id= ?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_act_gifts($act_id,$user_id){
        $this->sql = "select * from reserve_draw_log where user_id = ? and act_id=? and draw_type > 0";
        $this->params=array($user_id,$act_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_gift_code($gift_id){
        $this->sql = "select * from game_gifts where id = ?";
        $this->params=array($gift_id);
        $this->doResult();
        return $this->result;
    }


    public function get_gift_code_by_userid($gift_id,$user_id){
        $this->sql = "select * from game_gifts where buyer_id = ? and batch_id= ? ";
        $this->params = array($user_id,$gift_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_open_log($url,$act_id,$code,$ip){
        $this->sql = "insert into reserve_url_log(act_id,code,url,`time`) VALUES(?,?,?,?)";
        $this->params = array($act_id,$code,$url,time());
        $this->doInsert();
    }

    public function get_reserve_conut($act_id){
        $this->sql = "select count(1) as num from reserve_log where act_id = ?";
        $this->params = array($act_id);
        $this->doResult();
        return $this->result['num'];
    }

    public function get_game_articles($game_id){
        $data = $this->mmc->get("game_articles_act_".$game_id);
        if(!$data){
            $this->sql = "select * from articles where game_id=? order by id desc limit 4";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_articles_act_".$game_id, $data, 1,600);
        }
        return $data;
    }

    public function query_last_gift($batch_id){
        $this->sql = "select * from game_gifts where batch_id = ? and is_use = 0";
        $this->params=array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_game_gifts($id,$user_id){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params=array($user_id,time(),$id);
        $this->doExecute();
    }

    public function get_coupon_last_log($coupon_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id = ? and receive_time is NULL";
        $this->params=array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_type_coupon($value){
        $this->sql = "select * from coupon_tb where id = ?";
        $this->params=array($value);
        $this->doResult();
        return $this->result;
    }

    public function update_coupon_log_date($id,$endtime,$user_id){
        $this->sql = "update coupon_user_log_tb set user_id=?,receive_time=?,start_time=?,end_time=? where id = ? ";
        $this->params=array($user_id,time(),time(),$endtime,$id);
        $this->doExecute();
        return $this->result;
    }

    public function update_coupon_log($id,$user_id){
        $this->sql = "update coupon_user_log_tb set user_id=? where id = ? ";
        $this->params=array($user_id,$id);
        $this->doExecute();
        return $this->result;
    }

    public function get_reserve_draw_list($act_id){
        $this->sql = "select * from reserve_draw_log where act_id = ? and draw_type= 2";
        $this->params = array($act_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_wx_access_token(){
        $data = memcache_get($this->mmc, 'wx_access_token');
        return $data;
    }

    public function set_wx_access_token($data){
        memcache_set($this->mmc, "wx_access_token", $data, 1, 7200);
    }

    public function get_wx_access_jsapi_data($token){
        $data = memcache_get($this->mmc, 'jsapi_data_'.$token);
        return $data;
    }

    public function set_wx_access_jsapi_data($token,$data){
        memcache_set($this->mmc, 'jsapi_data_'.$token, $data, 1, 7200);
    }
}
?>
