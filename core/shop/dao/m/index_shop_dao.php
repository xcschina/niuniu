<?php
// --------------------------------------
//  店铺系统 <zbc> < 2016/4/14 >
// --------------------------------------

COMMON('dao');
class index_shop_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_shop_list($params){
        $this->sql = 'select * from shop where 1=1 and s_status=0 order by s_sort desc';
        isset($params['limit'])  && $this->sql .= ' limit '.$params['limit']; 
        $this->doResultList();
        $shop_list = $this->result;
        return $shop_list;
    }

    public function get_shop_info($shop_id=0){
        $this->sql = 'select * from shop where 1=1 and s_status=0 and s_id=?';
        $this->params = array($shop_id);
        $this->doResult();
        return $this->result;
    }

    public function get_master_service(){
        $data = $this->mmc->get("master_service_qq");
        if(!$data){
            $this->sql = "select * from admins where `group`='vip' and is_del=0 and qq<>''";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("master_service_qq", $data, 1, 600);
        }
        return $data;
    }

    public function get_master_setting(){
        $data = $this->mmc->get("master_setting");
        if(!$data){
            $this->sql = "select * from setting where id=1";
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("master_setting", $data, 1, 3600);
        }
        return $data;
    }

    public function shop_qq_decode($params=array()){
        list($qq_1, $qq_2, $qq_3) = explode(',', $params['s_qq']);
        unset($params['s_qq']);
        $params['s_qq'][0] = explode('|', $qq_1);
        $params['s_qq'][1] = explode('|', $qq_2);
        $params['s_qq'][2] = explode('|', $qq_3);
        return $params;
    }


}