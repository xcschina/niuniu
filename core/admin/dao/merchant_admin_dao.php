<?php
COMMON('niuniuDao');
class merchant_admin_dao extends niuniuDao {
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    public function get_charge_log($page,$params){
        $this->limit_sql = "SELECT m.*,a.app_name,ad.real_name FROM business_money_log m INNER JOIN business_orders o ON m.order_id=o.order_id 
                      INNER JOIN business_apps a ON o.app_id=a.app_id INNER JOIN admins ad ON o.guild_id=ad.id WHERE 1=1 ";
        if($params['order_id']){
            $this->limit_sql .= " and m.order_id = '".$params['order_id']."'";
        }
        if($params['type']){
            $this->limit_sql .= " and m.type = ".$params['type'];
        }
        if($params['app_id']){
            $this->limit_sql .= " and o.app_id = ".$params['app_id'];
        }
        if($params['guild_id']){
            $this->limit_sql .= " and ad.id = ".$params['guild_id'];
        }elseif($params['guild_id_list']){
            $this->limit_sql .= " and ad.id IN (".$params['guild_id_list'].")";
        }else{
            $this->limit_sql .= " and ad.id = ''";
        }
        if($params['start_time']){
            $this->limit_sql .= " and m.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and m.add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by m.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
}