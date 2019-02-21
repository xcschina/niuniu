<?php
COMMON('niuniuDao');
class gdt_ad_active_dao extends niuniuDao{

    public function __construct(){
        parent::__construct();
    }

    public function insert_feedback_record($params){
        $this->sql = 'INSERT INTO gdt_ad_data(muid,click_id,click_time,appid,advertiser_id,app_type,add_time)';
        $this->params = array($params['muid'],$params['click_id'],$params['click_time'],$params['appid'],$params['advertiser_id'],$params['app_type'],time());
        $this->doInsert();
    }

}