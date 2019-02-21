<?php
COMMON('niuniuDao','randomUtils');
class super_api_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
//        $this->redis = new Redis();
//        $this->redis->connect(REDIS_HOST,REDIS_PORT);
//        $this->redis->select(2);
    }

    public function get_ch_by_appid($super_id,$ch_code){
        $data = $this->mmc->get("super_ch_app_info".$super_id."_".$ch_code);
        if(empty($data)){
            $this->sql = "select s.* from channel_apps as ch left join super_apps as s on ch.super_id=s.app_id where ch.super_id=? AND ch.ch_code=?";
            $this->params = array($super_id,$ch_code);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("super_ch_app_info".$super_id."_".$ch_code, $data, 1,3600);
        }
        return $data;
    }


    public function get_suepr_info($app_id){
        $this->sql = "select * from super_apps where app_id=? AND `status`=1";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result ;
    }

    public function get_ch_info($super_id,$ch_code){
        $data = $this->mmc->get("channel_app_info".$super_id."_".$ch_code);
        if(empty($data)){
            $this->sql = "select * from channel_apps where super_id=? AND ch_code=?";
            $this->params = array($super_id,$ch_code);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("channel_app_info".$super_id."_".$ch_code, $data, 1,3600);
        }
        return $data;
    }

    public function get_ch_goods_info($super_id,$goods_code){
        $this->sql = "select * from super_app_goods where app_id=? AND good_code=? AND `status`='1'";
        $this->params = array($super_id,$goods_code);
        $this->doResult();
        return $this->result ;
    }

    public function get_ch_goodcode($super_id,$goodcode,$ch_code){
        $data = $this->mmc->get("ch_goodcode_".$super_id."_".$ch_code."_".$goodcode);
        if(!$data){
            $this->sql = "select ch.channel_goods,super_goods.* from super_goods_channel as ch left join super_app_goods as super_goods on ch.goods_id = super_goods.id  
                          where ch.app_id=? AND super_goods.good_code=? and ch.ch_code=? and ch.is_pub = '0'";
            $this->params = array($super_id,$goodcode,$ch_code);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("ch_goodcode_".$super_id."_".$ch_code."_".$goodcode, $data, 1,3600);
        }
        return $data;
    }

    public function get_tx_order($order_id){
        $this->sql = "select * from tx_order_log where niuorderid=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result ;
    }

    public function add_tx_order($params){
        $this->sql = "insert into tx_order_log(appid, channel, platform, openid,payToken,pf,pf_key,`timestamp`,niuorderid,sign,add_time,status)VALUE(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['appid'], $params['channel'], $params['platform'], $params['openid'],$params['payToken'],$params['pf'],$params['pf_key'],$params['timestamp'],$params['niuorderid'],$params['sign'],time(),1);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_tx_order_list(){
        $this->sql = "select * from tx_order_log WHERE status='1' order by id ASC";
        $this->params = array();
        $this->doResultList();
        return $this->result ;
    }

    public function get_tx_user($user_id){
        $this->sql = "select * from tx_user_info where openid=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result ;
    }

    public function add_tx_user($params){
        $this->sql = "insert into tx_user_info(appid, channel, platform, openid,pay_token,pf,pf_key,access_token,refresh_token,add_time)VALUE(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['appid'], $params['channel'], $params['platform'], $params['openid'],$params['payToken'],$params['pf'],$params['pf_key'],$params['accessToken'],$params['refreshToken'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_tx_user($params){
        $this->sql = "update tx_user_info set `pay_token`=?,pf=?,pf_key=?,access_token=?,refresh_token=?,up_time=? where openid = ? ";
        $this->params = array($params['payToken'], $params['pf'], $params['pf_key'], $params['accessToken'], $params['refreshToken'],time(),$params['openid']);
        $this->doExecute();
    }

    public function update_order_list($params){
        $this->sql = "UPDATE tx_order_log SET status=?,times=?,modify_time=?,remark=? WHERE niuorderid=?";
        $this->params = array($params['status'],$params['times'],$params['modify_time'],$params['remark'],$params['niuorderid']);
        $this->doExecute();
    }

    public function update_user_money($money,$openid){
        $this->sql = "UPDATE tx_user_info SET money=? WHERE openid=?";
        $this->params = array($money,$openid);
        $this->doExecute();
    }

    public function update_order_status($order_id){
        $this->sql = "UPDATE super_orders SET status=? WHERE order_id=?";
        $this->params = array(1,$order_id);
        $this->doExecute();
    }

    public function get_order_info($order_id){
        $this->sql = "select * from super_orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result ;
    }

    public function get_overtime_orders($user_id,$time){
        $this->sql = "SELECT count(1) as num from tx_order_log WHERE openid=? and `timestamp` >? AND `status`=3 ";
        $this->params = array($user_id,$time);
        $this->doResult();
        return $this->result;
    }

    public function update_overtime_orders($user_id,$time){
        $this->sql = "UPDATE tx_order_log set `status`=1,`times`=0 WHERE openid=? and `timestamp` >? AND `status`=3 ";
        $this->params = array($user_id,$time);
        $this->doExecute();
    }
}

?>
