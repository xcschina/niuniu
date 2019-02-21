<?php
COMMON('dao');
class login_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function login_log($id,$ip){
        $this->sql = "update admins set last_login='".strtotime("now")."' ,last_ip='".$ip."' where id=".$id;
        $this->doExecute();
    }

    public function get_user_by_account($account){
        $this->sql = "select * from admins where is_del=0 and  account='".$account."'";
        $this->doResult();
        return $this->result;
    }

    public function do_login_log($usr_name, $pwd, $desc, $ip, $browser, $usr_id = ''){
        $this->sql = 'insert into admin_login_log(usr_id,usr_name,`desc`,`time`,ip,browser,pwd) values(?,?,?,?,?,?,?)';
        $this->params = array($usr_id, $usr_name, $desc, date('Y-m-d H:i:s'), $ip, $browser, md5($pwd));
        $this->doExecute();
    }

    public function update_user_active($user_id, $time){
        $this->sql = "update admins set last_service_time=? where id=?";
        $this->params = array($time, $user_id);
        $this->doExecute();
    }
}