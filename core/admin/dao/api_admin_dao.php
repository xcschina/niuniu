<?php
COMMON('niuniuDao');
class api_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_pack_ch_list($app_id){
        $this->sql = "SELECT * from admins where user_code is not NUll and apps LIKE '%".$app_id."%'";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_pack_info($app_id){
        $this->sql = "SELECT * from apk_pack_tb where appid =?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_pack_info($data){
        $this->sql = "insert into apk_pack_tb(apk_url,appid,channels,add_time,update_time)values(?,?,?,?,?)";
        $this->params = array($data['apk_url'],$data['app_id'],$data['new_pack_ch'],time(),time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_pack_info($data,$id){
        $this->sql = "update apk_pack_tb set apk_url=? ,channels=? ,update_time=? where id=? ";
        $this->params = array($data['apk_url'],$data['new_pack_ch'],time(), $id);
        $this->doExecute();
    }

    public function get_guild_pack_info($app_id,$guild){
        $this->sql = "select * from guild_app_tb where app_id =? and guild_code=? ";
        $this->params = array($app_id,$guild);
        $this->doResult();
        return $this->result;
    }

    public function insert_guild_pack_info($app_id, $guild_code,$apk_size,$apk_url){
        $this->sql = "insert into guild_app_tb(app_id,guild_code,down_url,apk_size,`time`)values(?,?,?,?,?)";
        $this->params = array($app_id, $guild_code,$apk_url,$apk_size,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_guild_pack_info($app_id,$guild_code,$apk_size,$apk_url){
        $this->sql = "update guild_app_tb set down_url=?,apk_size=?,`time`=? where app_id=? and guild_code =?";
        $this->params = array($apk_url,$apk_size,time(),$app_id,$guild_code);
        $this->doExecute();
    }

    public function update_guild_pack_record($app_id, $guild_code,$apk_size,$apk_url,$status){
        $this->sql = "update guild_app_tb set down_url=?,apk_size=?,`time`=?,status=? where app_id=? and guild_code =?";
        $this->params = array($apk_url,$apk_size,time(),$status,$app_id,$guild_code);
        $this->doExecute();
        return $this->LAST_INSERT_ID;
    }

    public function get_cache(){
        return $this->mmc->get("pack_cache");
    }

    public function del_cache(){
        $this->mmc->delete("pack_cache");
    }

    public function set_cache(){
        $this->mmc->set("pack_cache",array("pack_cache"),1,8000);
    }

    public function update_pack_status($status,$id=1){
        $this->sql = "update guild_app_tb set status=? where id= ?";
        $this->params = array($status,$id);
        $this->doExecute();
    }
}