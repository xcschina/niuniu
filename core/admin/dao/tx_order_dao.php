<?php
COMMON('niuniuDao');
class tx_order_dao extends niuniuDao{

    public function __construct(){
        parent::__construct();
    }

    public function get_tx_order_list($params,$page){
        $this->limit_sql = "select a.*,s.app_name from tx_order_log a left join super_apps s on a.appid=s.app_id where 1=1";
        if($params['app_id']){
            $this->limit_sql .= " and a.appid = ".$params['app_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        if($params['platform']){
            $this->limit_sql .= " and a.platform = ".$params['platform'];
        }
        if($params['open_id']){
            $this->limit_sql .= " and a.openid = '".trim($params['open_id'])."'";
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.niuorderid = '".trim($params['order_id'])."'";
        }
        if($params['times'] && is_numeric($params['times']) || $params['times'] === '0'){
            $this->limit_sql .= " and a.times = ".trim($params['times']);
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.add_time < ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_super_order_info($order_id){
        $this->sql = "select * from super_orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_super_list(){
        $this->sql = "select * from super_apps where status=1 order by app_id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_tx_order_info($id){
        $this->sql = "select * from tx_order_log where id=? and status=3";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_tx_order($id){
        $this->sql = "update tx_order_log set status=1,times=0 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }

}