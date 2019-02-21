<?php
COMMON('niuniuDao');
class report_data_admin_dao extends niuniuDao {
    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    public function get_report_data($page,$params){
        $this->limit_sql = "SELECT id,imei,mac,type,status_code,time,environment FROM report_log_3 WHERE 1=1";
        if($params['imei']){
            $this->limit_sql .= " and imei='".$params['imei']."'";
        }
        if($params['mac']){
            $this->limit_sql .= " and mac='".$params['mac']."'";
        }
        if ($params['type']){
            $this->limit_sql .= " and type=".$params['type'];
        }
        if ($params['status']=='1'){
            $this->limit_sql .= " and status_code='0'";
        }elseif ($params['status']=='2'){
            $this->limit_sql .= " and status_code!='0'";
        }
        if ($params['envi']){
            $this->limit_sql .= " and environment=".$params['envi'];
        }
        if($params['start_time'] && $params['end_time']){
            $this->limit_sql .= " and time>=". strtotime($params['start_time'])." and time<=". strtotime($params['end_time']);
        }elseif($params['start_time'] && !$params['end_time']) {
            $this->limit_sql .= " and time>=".strtotime($params['start_time']);
        }elseif(!$params['start_time'] && $params['end_time']) {
            $this->limit_sql .= " and time<=".strtotime($params['end_time']);
        }
        $this->limit_sql .= " ORDER BY id DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }
}