<?php
COMMON('dao','randomUtils');
class site_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_service(){
        $data = $this->mmc->get("serviceqq");
        if(!$data){
            $this->sql = "select * from admins where `group`='vip' and is_del=0 and qq<>''";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("serviceqq", $data, 1, 3600);
        }
        return $data;
    }

    public function get_setting(){
        $this->sql = "select * from setting where id=1";
        $this->doResult();
        return $this->result;
    }
}