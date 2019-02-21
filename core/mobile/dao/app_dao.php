<?php
COMMON('dao','ApplicationQQ_api');
class app_dao extends Dao{
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game_info($game_id){
        $this->sql = "select * from 66app_game_tb where id = ? and is_del = 0";
        $this->params = array($game_id);
        $this->doResult();
        return $this->result;
    }

    //获取腾讯应用宝游戏详情
    public function get_game_info_tx($game_name){
        $qq = new ApplicationQQ_api();
        $res = $qq->getAppDetailBatchNew('["'.$game_name.'"]');
        return $res;
    }

    public function get_app_info($app_id){
        $data = $this->mmc->get("app_info"."_".$app_id);
        if(!$data) {
            $this->sql = "select * from niuniu.apps where app_id = ? and status = 1";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info"."_".$app_id,$data,1,120);
        }
        return $data;
    }
}
