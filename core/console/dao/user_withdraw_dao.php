<?php
COMMON('dao','randomUtils');
class user_withdraw_dao extends Dao{

    public function __construct(){
        parent::__construct();
    }

    public function update_user_withdraw_img($params){
        $this->sql="update user_with_draw set img_url=?,status=2 where id=?";
        $this->params=array($params['img'],$params['id']);
        $this->doExecute();
    }

    public function get_user_withdraw_list($params,$page){
        $this->limit_sql = "select a.*,b.login_name,c.pay_account from user_with_draw a left join user_info b on a.user_id=b.user_id left join user_detail c on a.user_id=c.user_id where 1=1" ;
        if($params['login_name']){
            $this->limit_sql=$this->limit_sql." and b.login_name like'%".$params['login_name']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
    public function add_user_balance_detail($params){
        $this->sql="insert into user_balance_detail(pay_mode,user_id,`type`,money,add_time)values(?,?,?,?,?)";
        $this->params=array($params['pay_mode'],$params['user_id'],$params['type'],$params['money'],time());
        $this->doInsert();
    }
    public function get_user_withdraw_detail($id){
        $this->sql = "select user_id,money from user_with_draw where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }
    public function get_user_balance($user_id){
        $this->sql = "SELECT balance FROM user_detail WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_user_balance($balance,$user_id){
        $this->sql = "update user_detail set balance=balance+? where user_id = ?";
        $this->params = array($balance,$user_id);
        $this->doExecute();
    }
    public function update_user_withdraw_status($params){
        $this->sql="update user_with_draw set status=? where id=?";
        $this->params=array($params['status'],$params['id']);
        $this->doExecute();
    }




}