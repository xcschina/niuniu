<?php
COMMON('dao','niuniuDao');
class pack_web_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_upload_game_list(){
        $this->sql = "select * from guild_app_tb where status = 3 order by time desc";
        $this->doResultList();
        return $this->result;
    }

    public function update_guild_pack_record($id,$status){
        $this->sql = "update guild_app_tb set `time`=?,status=? where id= ?";
        $this->params = array(time(),$status,$id);
        $this->doExecute();
    }

}