<?php
COMMON('dao');
class gdt_script_dao extends Dao{
    public function __construct(){
        parent::__construct();
    }

    public function get_ios_stats_device($parmas){
        //获取设备id
        $this->sql = 'SELECT Adtid,SID,RegTime FROM ios_stats_device WHERE AppID=? AND Channel=? AND RegTime>=? AND RegTime<?';
        $this->params = array($parmas['app_id'],$parmas['channel'],$parmas['begin_time'],$parmas['end_time']);
        $this->doResultList();
        return $this->result;
    }

    public function get_data_gdt($parmas){
        //取最近一条记录
        $this->sql = 'SELECT * FROM gdt_ad_data WHERE appid=? AND muid=? ORDER BY click_time DESC LIMIT 1';
        $this->params = array($parmas['app_id'],$parmas['muid']);
        $this->doResult();
        return $this->result;
    }

    public function insert_report_record($parmas){
        $this->sql = 'INSERT INTO gdt_ad_report(gdt_ad_id,app_id,ret,message)';
        $this->params = array($parmas['gdt_ad_id'],$parmas['app_id'],$parmas['ret'],$parmas['message']);
        $this->doInsert();
    }

    public function update_data_gdt($params){
        $this->sql = 'UPDATE gdt_ad_data SET device_id=? WHERE id=?';
        $this->params = array($params['device_id'],$params['id']);
        $this->doExecute();
    }
}