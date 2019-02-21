<?php
COMMON('niuniuDao');
class kpi_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admins";
    }

    public function get_guild_list(){
        $this->sql = "select * from admins where group_id=10";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_code($user_id){
        $this->sql = "select * from admins where group_id=10 and (id =? or p1=? or p2=?)";
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_down_log_num($code,$start_date,$end_date){
        $this->sql = "select count(*) as num from `66173`.general_down_log where code = ? AND add_time>=? AND add_time<=?";
        $this->params = array($code,$start_date,$end_date);
        $this->doResult();
        return $this->result;
    }

    public function get_user_role($app_id='',$code,$start_date,$end_date){
        $data = $this->mmc->get("kpi_user_count_".$app_id."_".$code."_".$start_date."_".$end_date);
        if(!$data){
            $this->sql = "SELECT COUNT(DISTINCT(UserID)) as num from  stats_user_app  where Channel ='".$code."' and  RegTime >=".$start_date." and RegTime <".$end_date ;
            if($app_id){
                $this->sql = $this->sql." and AppID =".$app_id;
            }
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("kpi_user_count_".$app_id."_".$code."_".$start_date."_".$end_date, $data, 1, 3600);
        }
        return $data;

    }

    public function get_diff_down_log_num($code,$start_date,$end_date){
        $this->sql = "select count(distinct ip) as num from `66173`.general_down_log where code = ? AND add_time>=? AND add_time<=?";
        $this->params = array($code,$start_date,$end_date);
        $this->doResult();
        return $this->result;
    }

    public function get_visit_log_num($code,$start_date,$end_date){
        $this->sql = "select count(*) as num from `66173`.general_visit_log as a left join `66173`.general_tb as b on a.relation_id = b.id  where b.code = ? AND a.add_time>=? AND a.add_time<=?";
        $this->params = array($code,$start_date,$end_date);
        $this->doResult();
        return $this->result;
    }

    public function get_visit_log_total($total){
        $this->sql = "select count(*) as num from `66173`.general_visit_log where code in (?)";
        $this->params = array($total);
        $this->doResult();
        return $this->result;
    }

    public function get_user_apps($user_id){
        $this->sql = "select * from admins where id= ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_apps_list($page,$app_id){
        $this->limit_sql = "select * from apps where 1=1 ";
        if($app_id){
            $this->limit_sql .= " and app_id = ".$app_id;
        }
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_list($page,$apps,$app_id){
        $this->limit_sql = "select * from apps where app_id in(".$apps.") ";
        if($app_id){
            $this->limit_sql .= " and app_id = ".$app_id;
        }
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_apps($app_id=''){
        $this->sql = "select * from apps where 1=1";
        if($app_id){
            $this->sql .= " and app_id in (".$app_id.")";
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_app_info($app_id){
        $data = $this->mmc->get("kpi_app_info_".$app_id);
        if(!$data){
            $this->sql = "select * from apps where app_id= ? and status = 1";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("kpi_app_info_".$app_id,$data,1,3600);
        }
        return $data;
    }

    public function get_lastest_seven_days_order($start_time,$end_time,$app_list,$channel){
        $this->sql = 'select count(*) as num from orders where pay_time <= '.$end_time.' and pay_time >= '.$start_time.' and `status` = 2 and app_id in ('.$app_list.')';
        if($channel){
            $this->sql .= ' and channel in ("'.$channel.'")';
        }
        $this->sql .= '  and buyer_id not in("15443","71","5632","13478","13474","164626")';
        $this->doResult();
        return $this->result;
    }

    public function get_pay_ammount($appid, $start, $end, $channels){
        $this->sql = "select sum(pay_money) as pay_ammount from orders where app_id= ".$appid." and status = 2 and pay_time >= ".$start." and pay_time <= ".$end;
        if(!empty($channels)){
            $this->sql = $this->sql .= " and channel in ('".$channels."')";
        }
        $this->doResult();
        return $this->result['pay_ammount'];
    }

    public function get_pay_count($appid, $start, $end, $channels,$pay_channel=''){
        $this->sql = "select sum(pay_money) as pay_count from orders where app_id= ".$appid." and status = 2 and pay_time >= ".$start." and pay_time <= ".$end ;
        if(!empty($pay_channel)){
            $this->sql = $this->sql .= " and pay_channel =".$pay_channel ;
        }
        if(!empty($channels)){
            $this->sql = $this->sql .= " and channel in ('".$channels."')";
        }
        $this->doResult();
        return $this->result['pay_count'];
    }

    public function get_game_channel($appid, $start_time){
        $this->sql = "SELECT channel,count(0) as ch_count FROM `stats_device` where appid=? and RegTime>? group by Channel ";
        $this->params = array($appid,$start_time);
        $this->doResultList();
        return $this->result;
    }

    public function get_old_role($appid, $start_time){
        $this->sql = "SELECT COUNT(DISTINCT (RoleID)) as role_count FROM `stats_user_app` AS st LEFT JOIN
        (SELECT userid,`do` FROM stats_user_op_log201811 WHERE appid=? AND addtime>=? AND `do` IN ('账号注册','一键账号注册')) AS u 
        ON st.UserID=u.userid WHERE st.appid=? AND st.RegTime> ? AND u.`do` IS NULL ";
        $this->params = array($appid,$start_time,$appid,$start_time);
        $this->doResult();
        return $this->result;
    }

    public function get_all_channel(){
        $data = $this->mmc->get("kpi_all_channel");
        if (!$data){
            $this->sql = "SELECT user_code FROM admins WHERE group_id = 12";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("kpi_all_channel", $data, 1, 7200);
        }
        return $data;
    }

    public function  get_app_channels($app_id){
        $data = $this->mmc->get("get_app_channels_".$app_id);
        if (!$data){
            $this->sql = "SELECT * from app_ios_pack WHERE app_id=? ";
            $this->params = array($app_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_app_channels_".$app_id, $data, 1, 600);
        }
        return $data;
    }
    //新增统计苹果充值总金额,订单数,付费用户数
    public function get_apple_pay_order_user_count($params){
        $this->sql = 'SELECT COUNT(*) AS user_no,SUM(b.user_order) AS order_count,SUM(b.user_pay) AS pay_count FROM (SELECT COUNT(1) AS user_order,SUM(a.pay_money) AS user_pay 
              FROM apple_order a INNER JOIN apps ap ON a.app_id=ap.app_id WHERE a.sandbox=2 AND a.status=3 AND a.pay_time>=? AND a.pay_time<? AND a.app_id IN ('.$params['app_list'].') 
              GROUP BY a.buyer_id) b';
        $this->params = array($params['start_time'],$params['end_time']);
        $this->doResult();
        return $this->result;
    }
    //根据appid统计苹果充值金额
    public function get_apple_pay_order_by_app_id($params){
        $this->sql = 'SELECT SUM(a.pay_money) AS app_pay_money,COUNT(*) AS app_count FROM apple_order a INNER JOIN apps ap ON a.app_id=ap.app_id WHERE a.sandbox=2 AND a.status=3 AND a.pay_time>=? 
                AND a.pay_time<?  AND a.app_id = ?';
        $this->params = array($params['start_time'],$params['end_time'],$params['app_id']);
        $this->doResult();
        return $this->result;
    }
    //根据时间统计苹果每个时间段的充值总金额，付费用户数
    public function get_apple_pay_by_date($params){
        $this->sql = 'SELECT a.date_group,SUM(a.user_date_pay_money) AS all_date_pay_money,COUNT(a.buyer_id) AS all_date_user_no FROM 
                      (SELECT SUM(pay_money) AS user_date_pay_money,DATE_FORMAT(FROM_UNIXTIME(pay_time),\'%Y-%m-%d\') AS date_group,buyer_id FROM apple_order WHERE app_id=? AND sandbox=2 AND status=3 AND 
                      pay_time>=? AND pay_time<? GROUP BY DATE_FORMAT(FROM_UNIXTIME(pay_time),\'%Y-%m-%d\'),buyer_id) a GROUP BY a.date_group';
        $this->params = array($params['app_id'],$params['start_time'],$params['end_time']);
        $this->doResultList();
        return $this->result;
    }

    public function get_extension_list(){
        $this->sql = " select * from admins where group_id=12 and user_code !='' and user_code is not null";
        $this->doResultList();
        return $this->result;
    }


}