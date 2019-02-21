<?php
COMMON('niuniuDao','randomUtils');
class super_pay_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_super_money_info($app_id,$money_id){
        $data = $this->mmc->get("super_money_id_".$app_id."_".$money_id);
        if(!$data) {
            $this->sql = "select b.app_name,a.* from super_app_goods as a inner join super_apps as b on a.app_id=b.app_id where a.app_id=? and a.good_code=? and a.status=1 order by good_price";
            $this->params = array($app_id,$money_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("super_money_id_".$app_id."_".$money_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_channel_sum_moeny($app_id,$channel){
        $this->sql = "SELECT sum(pay_money) as sum from super_orders WHERE app_id=? and channel=? and `status` > 0";
        $this->params = array($app_id,$channel);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_mmc_warn_money($id){
        $data = $this->mmc->get("get_mmc_warn_money_".$id);
        return $data;
    }

    public function set_mmc_warn_money($id,$data){
        $this->mmc->set("get_mmc_warn_money_".$id, $data, 1, 14400);
    }

    public function create_super_order($order){
        $this->sql = "insert into super_orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount,
                                        pay_money,discount,pay_price,status, buy_time, ip, serv_id, channel,payExpandData,serv_name)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_ch_by_appid($super_id,$ch_code){
        $this->sql = "select * from channel_apps where super_id=? AND ch_code=?";
        $this->params = array($super_id,$ch_code);
        $this->doResult();
        return $this->result ;
    }

    public function get_super_app_info($app_id){
        $data = $this->mmc->get("super_app_info".$app_id);
        if (!$data) {
            $this->sql = "select * from super_apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("super_app_info".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function set_usr_session($key, $data){
        $session_data = $this->mmc->get("mpay-session-".session_id());
        $session_data[$key] = $data;
        $this->mmc->set("mpay-session-".session_id(), $session_data, 1, 300);
    }

    public function get_usr_session($key){
        $session_data = $this->mmc->get("mpay-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }

    public function get_super_order($order_id){
        $this->sql = "select * from super_orders where order_id= ? ";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result ;
    }

    public function update_super_order_info($order_id,$pay_time,$ch_order){
        $this->sql = "update super_orders set `status`=?,pay_time=?,charge_time=?,ch_order=? where order_id = ? ";
        $this->params = array(1,$pay_time, time(), $ch_order,$order_id);
        $this->doExecute();
    }
}

?>
