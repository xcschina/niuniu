<?php
COMMON('niuniuDao');
class auto_pack_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "apps";
    }

    public function add_pack_record($params){
        $this->sql = "insert into guild_app_tb(guild_code,app_id,down_url,`time`,status)values(?,?,?,?,?)";
        $this->params = array($params['user_code'],$params['app_id'],$params['apk_url'],time(),0);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_user_info($user_id){
        $this->sql="select * from admins where id=?  ";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_pack_record($id,$status){
        $this->sql = "update guild_app_tb set `status`=?,pack_num=pack_num+1,`time`=? where id= ?";
        $this->params = array($status,time(),$id);
        $this->doExecute();
    }

    public function update_pack_log($id,$status){
        $this->sql = "update guild_app_tb set `status`=?,`time`=? where id= ?";
        $this->params = array($status,time(),$id);
        $this->doExecute();
    }

    public function get_app_pack_info($app_id){
        $this->sql = "select * from apk_pack_tb where appid=?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function update_app_pack_info($id,$channels){
        $this->sql = "update apk_pack_tb set channels=?,update_time=? where id= ?";
        $this->params = array($channels,time(),$id);
        $this->doExecute();
    }

    public function get_app_info($app_id){
        $this->sql = "SELECT * from apps where app_id =?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_app_pack_info($url,$app_id,$channels){
        $this->sql = "insert into apk_pack_tb(apk_url,appid,channels,add_time,update_time)values(?,?,?,?,?)";
        $this->params = array($url,$app_id,$channels,time(),time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_pack_in_list($app_id,$code,$status){
        $this->sql = "select * from guild_app_tb where app_id =? and guild_code= ? and `status`= ? ";
        $this->params = array($app_id,$code,$status);
        $this->doResult();
        return $this->result;
    }

    public function get_pack_record($app_id,$code){
        $this->sql = "select * from guild_app_tb where app_id =? and guild_code= ? ";
        $this->params = array($app_id,$code);
        $this->doResult();
        return $this->result;
    }

    public function get_pack_lsit($status){
        $this->sql = "select * from guild_app_tb where `status`= ? order by time";
        $this->params = array($status);
        $this->doResult();
        return $this->result;
    }
}