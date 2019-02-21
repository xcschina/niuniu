<?php
COMMON('niuniuDao');
class callback_dao extends niuniuDao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_channel_app_info($id){
        $this->sql = "select * from channel_apps where id= ? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result ;
    }

    public function get_channel_app_by_appid($appid){
        $this->sql = "select * from channel_apps where app_id= ? ";
        $this->params = array($appid);
        $this->doResult();
        return $this->result ;
    }

    public function get_ch_by_appid($super_id,$ch_code){
        $this->sql = "select * from channel_apps where super_id=? AND ch_code=?";
        $this->params = array($super_id,$ch_code);
        $this->doResult();
        return $this->result ;
    }


    public function get_ch_by_id($id){
        $this->sql = "select * from channel_apps where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result ;
    }

    public function get_super_info($app_id){
        $this->sql = "select * from super_apps where app_id= ? ";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result ;
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

    public function update_order_status($order_id,$status){
        $this->sql = "update super_orders set `status`=? where order_id = ? ";
        $this->params = array($status,$order_id);
        $this->doExecute();
    }

    public function get_goods_info($id){
        $this->sql = "select * from super_app_goods where id= ? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result ;
    }
}
?>
