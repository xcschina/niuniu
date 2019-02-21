<?php
COMMON('niuniuDao');
class login_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function login_log($id,$ip){
        $this->sql = "update admins set last_login='".strtotime("now")."' ,last_ip='".$ip."' where id=".$id;
        $this->doExecute();
    }

    public function get_user_by_account($account){
        $this->sql = "select a.*,b.type from admins a left join admins_relation_tb b on a.id=b.user_id where a.is_del=0 and a.account='".$account."'";
        $this->doResult();
        return $this->result;
    }

    public function do_login_log($usr_name, $pwd, $desc, $ip, $browser, $usr_id = 0){
        $this->sql = 'insert into admin_login_log(usr_id,usr_name,`desc`,`time`,ip,browser,pwd) values(?,?,?,?,?,?,?)';
        $this->params = array($usr_id, $usr_name, $desc, date('Y-m-d H:i:s'), $ip, $browser, md5($pwd));
        $this->doExecute();
    }

    public function update_user_active($user_id, $time){
        $this->sql = "update admins set last_service_time=? where id=?";
        $this->params = array($time, $user_id);
        $this->doExecute();
    }

    public function get_user_by_id($user_id){
        $this->sql = "select * from admins where is_del=0 and id=? ";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function set_online_info($key,$data){
        memcache_set($this->mmc,$key,$data,0,6);
    }

    public function get_online_info($key){
        return memcache_get($this->mmc,$key);
    }

    public function update_relation_info($user_id){
        $this->sql = "update admins_relation_tb set is_online = 2 where user_id =?" ;
        $this->params = array($user_id);
        $this->doExecute();
    }
}