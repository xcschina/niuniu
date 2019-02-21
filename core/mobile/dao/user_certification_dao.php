<?php
COMMON('dao','randomUtils');
class user_certification_dao extends Dao{

    public function __construct(){
        parent::__construct();
    }
    public function insert_user_certification($params){
        $this->sql="insert into user_certification(user_id,real_name,id_card,add_time) values (?,?,?,?)";
        $this->params=array($params['user_id'],$params['real_name'],$params['id_card'],time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }
    public function update_user_security_mobile($mobile,$user_id){
        $this->sql="update user_detail set security_mobile=? where user_id=?";
        $this->params=array($mobile,$user_id);
        $this->doExecute();
    }
    public function update_user_pay_passward($pay_passward,$user_id){
        $this->sql="update user_detail set pay_password=? where user_id=?";
        $this->params=array($pay_passward,$user_id);
        $this->doExecute();
    }
    public function update_user_pay_account_and_name($pay_account,$pay_name,$user_id){
        $this->sql="update user_detail set pay_account=?,pay_name=? where user_id=?";
        $this->params=array($pay_account,$pay_name,$user_id);
        $this->doExecute();
    }
    public function get_security_mobile($user_id){
        $this->sql = "select security_mobile as num from user_info where user_id=?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function add_send_sms_log($ip,$phone,$code='',$today,$HTTP_REFERER,$result){
        $this->sql="insert into send_sms_log(ip,mobile,sms_code,add_date,http_referer,result,add_time) values (?,?,?,?,?,?,?)";
        $this->params=array($ip,$phone,$code,$today,$HTTP_REFERER,$result,time());
        $this->doInsert();
    }
    public function get_ip_count($ip,$today){
        $this->sql = "select count(1) as num from send_sms_log where ip=? and add_date= ?";
        $this->params=array($ip,$today);
        $this->doResult();
        return $this->result;
    }

    public function get_phone_count($phone,$today){
        $this->sql = "select count(1) as num from send_sms_log where mobile=? and add_date= ?";
        $this->params=array($phone,$today);
        $this->doResult();
        return $this->result;
    }
    public function get_user_withdraw_info($user_id){
        $this->sql = "select * from user_with_draw where user_id=?";
        $this->params=array($user_id);
        $this->doResultList();
        return $this->result;
    }
    public function get_user_balance_detail($user_id){
        $this->sql = "select * from user_balance_detail where user_id=?";
        $this->params=array($user_id);
        $this->doResultList();
        return $this->result;

    }
    public function get_user_pay_passward($user_id){
        $this->sql = "select pay_passward as num from user_detail where user_id=?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function insert_user_withdraw($params){
        $this->sql="insert into user_with_draw(user_id,money,charge_money,actual_money,add_time) values (?,?,?,?,?)";
        $this->params=array($params['user_id'],$params['money'],$params['charge_money'],$params['actual_money'],time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }
    public function get_user_certification($user_id){
        $this->sql    = "select count(1) as num from user_certification where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function get_account_info($user_id){
        $this->sql    = "select c.mobile,a.pay_password,a.security_mobile,a.pay_account,a.pay_name,a.balance,b.real_name,b.id_card from user_detail a left join user_certification b on a.user_id=b.user_id left join user_info c on a.user_id=c.user_id where a.user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function insert_user_detail($user_id){
        $this->sql="insert into user_detail(user_id) values (?)";
        $this->params=array($user_id);
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }
    public function get_user_detail($user_id){
        $this->sql    = "select count(1) as num from user_detail where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function update_user_balance($balance,$user_id){
        $this->sql = "update user_detail set balance=balance-? where user_id = ?";
        $this->params = array($balance,$user_id);
        $this->doExecute();
    }

}