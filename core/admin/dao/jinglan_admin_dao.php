<?php
COMMON('dao');
class jinglan_admin_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function list_data($page,$params){
        $this->limit_sql = "select * from jinglan where `type`=1";
        if($params['order_id']){
            $this->limit_sql .= " and order_id = '".trim($params['order_id'])."'";
        }
        if($params['qq']){
            $this->limit_sql .=" and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql .= " and `status` = ".$params['status'];
        }
        if($params['game_id'] && is_numeric($params['game_id']) || $params['game_id'] === "0"){
            $this->limit_sql .= " and game_id = ".$params['game_id'];
        }
        if($params['operation_id']){
            $this->limit_sql .=" and operation_id = ".$params['operation_id'];
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

    public function get_operation_name(){
        $this->sql = "SELECT a.id,a.real_name FROM jinglan h INNER JOIN niuniu.admins a ON h.operation_id=a.id GROUP BY h.operation_id";
        $this->doResultList();
        return $this->result;
    }

    public function insert_order_by_query($order_id, $params, $jinglan_order_id,$status){
        $this->sql = "insert into jinglan(order_id,amount,qq,jinglan_order_id,add_time,operation_id,game_id,status,price)values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $params['amount'], $params['qq'], $jinglan_order_id, time(), $_SESSION['usr_id'],$params['game_id'],$status,$params['price']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_order($order_id,$params,$jinglan_order_id){
        $this->sql = "insert into jinglan(order_id,amount, price, qq, jinglan_order_id, add_time, operation_id,game_id)values(?,?,?,?,?,?,?,?)";
        $this->params = array($order_id,$params['amount'], $params['price'], $params['qq'], $jinglan_order_id, time(), $_SESSION['usr_id'],$params['game_id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_ip_session($ip){
        $data = $this->mmc->get("jinglan_".$ip);
        return $data;
    }

    public function set_ip_session($ip,$data){
        $this->mmc->set("jinglan_".$ip,$data,1,600);
    }

    public function get_list_all($params){
        $this->sql = "select * from jinglan where `type`=1";
        if($params['order_id']){
            $this->sql .= " and order_id = '".trim($params['order_id'])."'";
        }
        if($params['qq']){
            $this->sql .=" and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->sql .= " and `status` = ".$params['status'];
        }
        if($params['game_id'] && is_numeric($params['game_id']) || $params['game_id'] === "0"){
            $this->sql .= " and game_id = ".$params['game_id'];
        }
        if($params['operation_id']){
            $this->sql .=" and operation_id = ".$params['operation_id'];
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

    public function get_qb_price(){
        $this->sql = "select * from qb_price_log where price and qb_type=1 order by add_time desc";
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from niuniu.admins where id= ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($id){
        $this->sql = "select * from jinglan where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_jinglan_price($params){
        $this->sql = "update jinglan set price=? where id=?";
        $this->params = array($params['new_price'],$params['id']);
        $this->doExecute();
    }

    public function insert_qb_price_log($params,$user_id){
        $this->sql = "insert into qb_price_log (price,operator_id,order_id,add_time)values(?,?,?,?)";
        $this->params = array($params['new_price'],$user_id,$params['id'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_order_amount($qq,$start_time){
        $this->sql = "select sum(amount) as amount from jinglan where qq = ? and status <=3 and `type`=1 and add_time >= ? and add_time <= ?";
        $this->params = array($qq,$start_time,time());
        $this->doResult();
        return $this->result;
    }
}