<?php
COMMON('niuniuDao');
class game_site_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game_info($id){
//        $data = memcache_delete($this->mmc, "web_app_info".$id);
        $data = memcache_get($this->mmc, "web_app_info".$id);
        if (!$data) {
            $this->sql = "select * from apps where app_id=?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_info".$id, $data, 1, 600);
        }
        return $data;
    }

    public function get_exchanges($game_id){
//        memcache_delete($this->mmc, "web_app_exchanges".$game_id);
//        $data = memcache_get($this->mmc, "web_app_exchanges".$game_id);
        if (!$data) {
//            $this->sql = "select * from app_goods where app_id=? and status = '1' ";
            $this->sql = "select * from app_goods where app_id=? and status = '1' and rec_type = '1' ";
            if($game_id=='1059'){
                $this->sql.= " and good_amount > 9999 ";
            }
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_exchanges".$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_exchanges_by_goodid($game_id,$goodid){
        memcache_delete($this->mmc, "web_app_exchanges".$game_id."_".$goodid);
        $data = memcache_get($this->mmc, "web_app_exchanges".$game_id."_".$goodid);
        if (!$data) {
            $this->sql = "select * from app_goods where app_id=? and id=?";
            $this->params = array($game_id,$goodid);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_exchanges".$game_id."_".$goodid, $data, 1, 600);
        }
        return $data;
    }

    public function get_money($money_id){
        $data = memcache_get($this->mmc, "web_app_money".$money_id);
        if (!$data) {
            $this->sql = "select * from app_goods where id=?";
            $this->params = array($money_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_money".$money_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_money_info($app_id,$good_code){
//        $data = memcache_get($this->mmc, "web_app_money".$app_id.'_'.$good_code);
        if (!$data) {
            $this->sql = "select * from app_goods where app_id=? and good_code=? and `status` = 1";
            $this->params = array($app_id,$good_code);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_money".$app_id.'_'.$good_code, $data, 1, 600);
        }
        return $data;
    }

    public function create_order($order){
        $this->sql = "insert into orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount, 
                                        pay_money,status, buy_time, ip, serv_id, channel,payExpandData,pay_from,`mac`,idfa,idfv,web_channel)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_order_num($params){
        $this->sql = "select count(*) as num from orders where role_id=? and serv_id = ? and product_id = ? and app_id = ? and status = ?";
        $this->params = array($params['player_id'],$params['serv_id'],$params['money_id'],$params['game_id'],2);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($order_id){
        $this->sql = "select * from orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function update_bjb_pay_info($order_id){
        $this->sql = "update orders set pay_time=?,charge_time=?,status=? where order_id=?";
        $this->params = array(strtotime("now"),strtotime("now"),1,$order_id);
        $this->doExecute();
    }

    public function get_white_sid($sid){
        $this->sql = "select * from `beijing`.record_device where device=? and is_del='0'";
        $this->params = array($sid);
        $this->doResult();
        return $this->result;
    }
}
?>
