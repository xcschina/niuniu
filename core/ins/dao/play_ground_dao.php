<?php
COMMON('niuniuDao');
class play_ground_dao extends niuniuDao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
//        $this->redis = new Redis();
//        $this->redis->connect(REDIS_HOST,REDIS_PORT);
//        $this->redis->select(2);
    }

    public function set_usr_session($key, $data){
        $session_data = memcache_get($this->mmc, "feedback-session-".session_id());
        $session_data[$key] = $data;
        memcache_set($this->mmc, "feedback-session-".session_id(), $session_data, 1, 600);
    }

    public function get_usr_session($key){
        $session_data = memcache_get($this->mmc, "feedback-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }

    public function get_login_reward($user_id,$start_time,$end_time){
        $this->sql = "select * from integral_log where (add_time between ? and ?) and user_id =? and `type` = 1";
        $this->params = array($start_time,$end_time,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($user_id){
//        $data = memcache_get($this->mmc, "user_info_".$user_id);
        if(!$data){
            $this->sql = "select a.*,b.integral,b.exp from `66173`.user_info a left join `66173`.user_relation_tb b on a.user_id = b.user_id where a.user_id = ?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "user_info_".$user_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_user($user_id){
//        $data = memcache_get($this->mmc, "user_info_".$user_id);
        if(!$data){
            $this->sql = "select * from `66173`.user_info where user_id = ?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "user_info_".$user_id, $data, 1, 600);
        }
        return $data;
    }

    public function insert_login_reward($user,$app_id,$integral){
        $this->sql = "insert into integral_log(user_id,`type`,integral,add_time,app_id,sid) values (?,?,?,?,?,?)";
        $this->params = array($user['user_id'],1,$integral,time(),$app_id,$user['sid']);
        $this->doExecute();
    }

    public function update_user_integral($user_id,$integral){
        $this->sql = "update `66173`.user_relation_tb set integral = ? where user_id =?";
        $this->params = array($integral,$user_id);
        $this->doExecute();
        memcache_delete($this->mmc,"user_info_".$user_id);
    }

    public function get_app_list(){
        $this->sql = "select a.*,b.nnb_scale,b.relation_id from apps a left join apps_relation_tb b on a.app_id = b.app_id where a.status = 1 and a.web_serv_url != '' order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_info($app_id){
        $this->sql = "select a.*,b.nnb_scale,b.relation_id from apps a left join apps_relation_tb b on a.app_id = b.app_id where a.app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_good_list($app_id){
        $data = memcache_get($this->mmc, "web_app_goods".$app_id);
        if (!$data) {
            $this->sql = "select * from app_goods where app_id=? and status = '1' and rec_type = '1' ";
            $this->params = array($app_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_goods".$app_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_app_message($app_id){
        $this->sql = "select * from apps_relation_tb where app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_goods_info($goods_id){
        $this->sql = "select * from app_goods where id = ?";
        $this->params = array($goods_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_order($order){
        $this->sql = "insert into orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount, 
                                        pay_money,status, buy_time, ip, serv_id, channel,payExpandData,pay_from,ch_type,`mac`,idfa,idfv,web_channel,consume)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_integral_order($user_id){
        $this->sql = "select a.*,b.app_icon from orders a left join apps b on a.app_id = b.app_id where a.ch_type = 5 and a.buyer_id = ? order by a.buy_time desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_integral($params,$user){
        $this->sql = "insert into integral_log(user_id,`type`,integral,add_time,app_id,sid) values (?,?,?,?,?,?)";
        $this->params = array($params['user_id'],$params['type'],$params['integral'],time(),$params['app_id'],$user['sid']);
        $this->doExecute();
    }

    public function get_money_integral(){
        $this->sql = 'select * from nnb_integral where is_del = 0';
        $this->doResultList();
        return $this->result;
    }

    public function get_user_message($user_id){
        $this->sql = 'select * from `66173`.user_relation_tb where user_id =?';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_user_message($user_id,$integral){
        $this->sql = 'insert into `66173`.user_relation_tb (user_id,integral,add_time)values(?,?,?)';
        $this->params = array($user_id,$integral,time());
        $this->doInsert();
    }
}