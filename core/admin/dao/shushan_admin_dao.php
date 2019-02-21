<?php
COMMON('dao');
class shushan_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "shushan";
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function list_data($page,$params){
        $this->limit_sql = "select * from shushan where 1=1";
        if($params['order_id']){
            $this->limit_sql = $this->limit_sql." and order_id = '".trim($params['order_id'])."'";
        }
        if($params['product_id']){
            $this->limit_sql = $this->limit_sql." and product_id = ".$params['product_id'];
        }
        if($params['qq']){
            $this->limit_sql = $this->limit_sql." and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql = $this->limit_sql." and `status` = ".$params['status'];
        }
        if($params['game_id_search'] && is_numeric($params['game_id_search']) || $params['game_id_search'] === "0"){
            $this->limit_sql = $this->limit_sql." and `game_id` = ".$params['game_id_search'];
        }
        if($params['start_time']){
            $this->limit_sql = $this->limit_sql." and  add_time>=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql = $this->limit_sql." and  add_time<".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_order($order_id, $params, $price, $shushan_order_id){
        $this->sql = "insert into shushan(order_id, product_id, amount, price, qq, shushan_order_id, add_time, operation_id,game_id)values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $params['product_id'], $params['amount'], $price, $params['qq'], $shushan_order_id, strtotime("now"), $_SESSION['usr_id'],$params['game_id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_order_by_query($order_id, $params, $price, $shushan_order_id,$status){
        $this->sql = "insert into shushan(order_id, product_id, amount, price, qq, shushan_order_id, add_time, operation_id,game_id,status)values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $params['product_id'], $params['amount'], $price, $params['qq'], $shushan_order_id, strtotime("now"), $_SESSION['usr_id'],$params['game_id'],$status);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_list_all($params){
        $this->sql = "select * from shushan where 1=1";
        if($params['order_id']){
            $this->sql = $this->sql." and order_id = '".trim($params['order_id'])."'";
        }
        if($params['product_id']){
            $this->sql = $this->sql." and product_id = ".$params['product_id'];
        }
        if($params['qq']){
            $this->sql = $this->sql." and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->sql = $this->sql." and `status` = ".$params['status'];
        }
        if($params['game_id_search'] && is_numeric($params['game_id_search']) || $params['game_id_search'] === "0"){
            $this->sql = $this->sql." and `game_id` = ".$params['game_id_search'];
        }
        if($params['start_time']){
            $this->sql = $this->sql." and  add_time>=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and  add_time<".strtotime($params['end_time']);
        }
        $this->sql = $this->sql." order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_order_amount($qq,$start_time){
        $this->sql = "select sum(amount) as amount from shushan where qq = ? and status < 3 and add_time >= ? and add_time <= ?";
        $this->params = array($qq,$start_time,time());
        $this->doResult();
        return $this->result;
    }

}