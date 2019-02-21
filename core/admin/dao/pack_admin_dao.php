<?php
COMMON('niuniuDao');
class pack_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "apps";
    }

    public function get_pack_record($app_id,$guild_id){
        $this->sql = "select * from apk_pack_log where app_id= ? and guild_id= ? ";
        $this->params=array($app_id,$guild_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_pack_log($code,$app_id,$url,$guild_id,$status){
        $this->sql = "insert into apk_pack_log(code,app_id,apk_url,guild_id,status,add_time)values(?,?,?,?,?,?)";
        $this->params = array($code,$app_id,$url,$guild_id,$status,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}