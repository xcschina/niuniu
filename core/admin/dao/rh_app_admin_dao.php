<?php
COMMON('niuniuDao');
class rh_app_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_all_rh_apps(){
        $this->sql = "select * from super_apps WHERE status = 1";
        $this->doResultList();
        return $this->result;
    }

    public function get_all_ch(){
        $this->sql = "select channel,ch_code from channel_apps group by ch_code";
        $this->doResultList();
        return $this->result;
    }

    public function get_channel_info($ch){
        $data = $this->mmc->get("get_channel_info_".$ch);
        if(!$data) {
            $this->sql = "select * from channel_apps where ch_code= ? ";
            $this->params = array($ch);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("get_channel_info_".$ch, $data, 1, 3600);
        }
        return $data;
    }

}