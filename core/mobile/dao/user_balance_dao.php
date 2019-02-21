<?php
COMMON('dao','randomUtils');
class user_balance_dao extends Dao{

    public function __construct(){
        parent::__construct();
    }

    public function get_order(){
        $this->sql = "select * from orders where status=1";
        $this->doResult();
        return $this->result;
    }
    public function get_product($id){
        $this->sql = "select user_id from products where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function update_user_lock($status, $user_id){
        $this->sql    = "update user_detail set pay_lock=? where user_id=?";
        $this->params = array($status, $user_id);
        $this->doExecute();
    }
    public function get_user_lock($user_id){
        $this->sql    = "select pay_lock from user_detail where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function update_user_balance($balance,$user_id){
        $this->sql    = "update user_detail set balance=balance+? where user_id = ?";
        $this->params = array($balance,$user_id);
        $this->doExecute();
    }
    public function get_user_balance_by_order_id($order_id){
        $this->sql = "select * from user_balance_detail where status=3 and order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }
    public function get_balance($user_id){
        $this->sql    = "SELECT balance FROM user_detail WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function update_user_balance_status($status, $order_id){
        $this->sql    = "update user_balance_detail set status=? where order_id=?";
        $this->params = array($status, $order_id);
        $this->doExecute();
    }
    public function get_user_balance_by_type(){
        $this->sql = "select * from user_balance_detail where status=2 and `type`=4";
        $this->doResultList();
        return $this->result;
    }

    public function add_user_balance_detail($params){
        $this->sql    = "insert into user_balance_detail(order_id,pay_mode,user_id,`type`,money,add_time,status)values(?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$params['pay_mode'], $params['user_id'], $params['type'], $params['money'], time(), $params['status']);
        $this->doInsert();
    }




}