<?php
COMMON('niuniuDao');
class rh_kpi_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admins";
    }

    public function get_rh_apps($app_id=''){
        $this->sql = "select * from super_apps where 1=1 and status = 1";
        if($app_id){
            $this->sql .= " and app_id =".$app_id;
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_rh_app_info($app_id){
        $this->sql = "select * from super_apps where app_id= ? and status = 1";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_lastest_seven_days_order($start_time,$end_time,$app_list,$channel){
        $this->sql = 'select count(*) as num from super_orders where pay_time <= '.$end_time.' and pay_time >= '.$start_time.' and `status` = 2 and app_id in ('.$app_list.')';
        if($channel){
            $this->sql .= ' and channel in ("'.$channel.'")';
        }
        $this->sql .= ' and buyer_id not in("71")';
        $this->doResult();
        return $this->result;
    }

    public function get_pay_ammount($appid, $start, $end, $channels){
        $this->sql = "select sum(pay_money) as pay_ammount from super_orders where app_id= ".$appid." and status = 2 and pay_time >= ".$start." and pay_time <= ".$end;
        if(!empty($channels)){
            $this->sql = $this->sql .= " and channel in ('".$channels."')";
        }
        $this->doResult();
        return $this->result['pay_ammount'];
    }

    public function get_pay_count($appid, $start, $end, $channels){
        $this->sql = "select count(*) as pay_count from (";
        $this->sql = $this->sql .= " select buyer_id from super_orders where app_id= ".$appid." and status = 2 and pay_time >= ".$start." and pay_time <= ".$end;
        if(!empty($channels)){
            $this->sql = $this->sql .= " and channel in ('".$channels."')";
        }
        $this->sql = $this->sql .=" group by buyer_id ) as pay_count";
        $this->doResult();
        return $this->result['pay_count'];
    }
}