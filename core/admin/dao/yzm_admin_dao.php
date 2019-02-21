<?php
COMMON('dao');
class yzm_admin_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function list_data($page,$params,$type){
        $this->limit_sql = "select * from yunzhimeng where `type`=".$type;
        if($params['order_id']){
            $this->limit_sql .= " and order_id = '".trim($params['order_id'])."'";
        }
        if($params['product_id']){
            $this->limit_sql .= " and product_id = ".$params['product_id'];
        }
        if($params['qq']){
            $this->limit_sql .= " and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql .= " and `status` = ".$params['status'];
        }
        if($params['game_id'] && is_numeric($params['game_id']) || $params['game_id'] === "0"){
            $this->limit_sql .= " and game_id = ".$params['game_id'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and  add_time>=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and  add_time<".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from `niuniu`.admins where id =?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_amount($params,$start_time){
        $this->sql = "select sum(amount) as amount from yunzhimeng where qq = ? and `type`=? and status = 1 or (status = 0 and callback_time is NULL ) and add_time >= ?";
        $this->params = array($params['qq'],$params['type'],$start_time);
        $this->doResult();
        return $this->result;
    }

    public function insert_order($order_id, $params, $price, $merchant_order_id){
        $this->sql = "insert into yunzhimeng(order_id, product_id, amount, price, qq, merchant_order_id, add_time, operation_id,game_id,`type`)values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $params['product_id'], $params['amount'], $price, $params['qq'], $merchant_order_id, strtotime("now"), $_SESSION['usr_id'],$params['game_id'],$params['type']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_order_by_query($order_id, $params, $price, $merchant_order_id,$status){
        $this->sql = "insert into yunzhimeng(order_id, product_id, amount, price, qq, merchant_order_id, add_time, operation_id,game_id,status,`type`)values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $params['product_id'], $params['amount'], $price, $params['qq'], $merchant_order_id, strtotime("now"), $_SESSION['usr_id'],$params['game_id'],$status,$params['type']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_list_all($params){
        $this->sql = "select * from yunzhimeng where `type`=".$params['type'];
        if($params['order_id']){
            $this->sql .= " and order_id = '".trim($params['order_id'])."'";
        }
        if($params['product_id']){
            $this->sql .= " and product_id = ".$params['product_id'];
        }
        if($params['qq']){
            $this->sql .= " and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->sql .= " and `status` = ".$params['status'];
        }
        if($params['game_id'] && is_numeric($params['game_id']) || $params['game_id'] === "0"){
            $this->sql .= " and game_id = ".$params['game_id'];
        }
        if($params['start_time']){
            $this->sql .= " and  add_time>=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and  add_time<".strtotime($params['end_time']);
        }
        $this->sql .= " order by id desc";
        $this->doResultList();
        return $this->result;
    }
}