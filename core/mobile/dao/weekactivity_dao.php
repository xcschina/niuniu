<?php
COMMON('dao','niuniuDao');
class weekactivity_dao extends niuniuDao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_app_info_by_appleid($app_id,$apple_id){
        $data = $this->mmc->get("apple_info_".$apple_id);
        if(!$data){
            $this->sql = "select a.*,a.game_name as app_name,b.web_url from app_ios_pack a left join apps b on a.app_id = b.app_id where a.app_id =? and a.apple_id=? ";
            $this->params = array($app_id,$apple_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("apple_info_".$apple_id,$data,1,3600);
        }
        return $data;
    }

    public function get_app_info($game_id){
        $data = memcache_get($this->mmc,'game_app_info'.$game_id);
        if(!$data){
            $this->sql = "select * from apps where app_id=? ";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('game_app_info'.$game_id, $data, 1, 600);
        }
        return $data;
    }


}