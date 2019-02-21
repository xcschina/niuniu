<?php
COMMON('dao');
class youxi9_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_youxi9_list($params,$page,$pay_way){
        $this->limit_sql = "select * from youxi9 where pay_way=".$pay_way;
        if($params['order_id']){
            $this->limit_sql .= " and order_id = '".trim($params['order_id'])."'";
        }
        if($params['qq']){
            $this->limit_sql .= " and qq = '".trim($params['qq'])."'";
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
        if($params['merchant_order_id']){
            $this->limit_sql .= " and merchant_order_id = '".trim($params['merchant_order_id'])."'";
        }
        $this->limit_sql .= " order by add_time desc,id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_operation_name(){
        $this->sql = "SELECT a.id,a.real_name FROM youxi9 h INNER JOIN niuniu.admins a ON h.operation_id=a.id GROUP BY h.operation_id";
        $this->doResultList();
        return $this->result;
    }

    public function get_order_amount($params,$start_time){
        $this->sql = "select sum(amount) as amount from youxi9 where qq = ? and status <3 and pay_way=? and add_time >= ? and add_time <= ?";
        $this->params = array($params['qq'],$params['pay_way'],$start_time,time());
        $this->doResult();
        return $this->result;
    }

    public function insert_order_by_query($order_id, $params, $info,$goods_id){
        $this->sql = "insert into youxi9(order_id,amount,qq,merchant_order_id,add_time,operation_id,game_id,status,price,msg,pay_way,goods_id)values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $params['amount'], $params['qq'], $info['productId'], time(), $_SESSION['usr_id'],$params['game_id'],$info['status'],$params['price'],$info['msg'],$params['pay_way'],$goods_id);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_order($order_id,$params,$merchant_order_id,$goods_id){
        $this->sql = "insert into youxi9(order_id,amount, price, qq, merchant_order_id, add_time, operation_id,game_id,pay_way,goods_id)values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id,$params['amount'], $params['price'], $params['qq'], $merchant_order_id, time(), $_SESSION['usr_id'],$params['game_id'],$params['pay_way'],$goods_id);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_list_all($params){
        $this->sql = "select * from youxi9 where pay_way = ".$params['pay_way'];
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
        if($params['merchant_order_id']){
            $this->sql .= " and merchant_order_id = '".trim($params['merchant_order_id'])."'";
        }
        $this->sql .= " order by add_time desc,id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_qb_info($id,$ch){
        $this->sql = "select * from ".$ch." where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($id){
        $this->sql = "select * from niuniu.admins where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_qb_status($params){
        $this->sql = "update ".$params['ch']." set status = ?,callback_time=?,merchant_order_id=? where id = ?";
        $this->params = array($params['status_after'],time(),$params['merchant_order_id'],$params['id']);
        $this->doExecute();
    }

    public function insert_operation_log($params,$user_id){
        $this->sql = "insert into qb_operation_log (operation_id,add_time,merchant_order_id,order_id,channel,status_before,status_after) values (?,?,?,?,?,?,?)";
        $this->params = array($user_id,time(),$params['merchant_order_id'],$params['id'],$params['ch'],$params['status_before'],$params['status_after']);
        $this->doInsert();
    }

    public function insert_youxi9_order($params,$user_id){
        $this->sql = "insert into youxi9(order_id,amount,merchant_order_id,status,add_time,qq,operation_id,price,pay_way,msg,`from`,money_status)values (?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$params['amount'],$params['merchant_order_id'],$params['status'],strtotime($params['add_time']),$params['qq'],$user_id,$params['price'],$params['pay_way'],$params['msg'],$params['from'],$params['money_status']);
        $this->doInsert();
    }

    public function get_order_info($m_order_id){
        $this->sql = "select * from youxi9 where merchant_order_id = ?";
        $this->params = array($m_order_id);
        $this->doResult();
        return $this->result;
    }

    public function update_youxi9_status($params){
        $this->sql = "update youxi9 set status = ?,callback_time=? where merchant_order_id=?";
        $this->params = array($params['status'],time(),$params['merchant_order_id']);
        $this->doExecute();
    }
}