<?php
COMMON('niuniuDao','randomUtils');
class index_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_new_game(){
        $data = $this->mmc->get("new_game");
        if(!$data){
            $this->sql = "select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id where a.is_new = '0' and a.is_del ='0' order by a.add_time desc limit 9";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("new_game",$data,1,120);
        }
        return $data;
    }

    public function get_banner_list(){
        $this->sql = "select * from apps_banner where is_del = 0 order by id desc limit 6";
        $this->doResultList();
        return $this->result;
    }

    public function get_wx_access_token(){
        $data = $this->mmc->get('wx_access_token');
        return $data;
    }

    public function set_wx_access_token($data){
        $this->mmc->set( "wx_access_token", $data, 1, 7200);
    }

    public function get_wx_access_jsapi_data($token){
        $data = $this->mmc->get( 'jsapi_data_'.$token);
        return $data;
    }

    public function set_wx_access_jsapi_data($token,$data){
        $this->mmc->set( 'jsapi_data_'.$token, $data, 1, 7200);
    }

}