<?php
COMMON('niuniuDao');
class qa_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_h5_device($page,$params){
        $this->limit_sql = "select * from h5_stats_device where 1=1";
        if($params['app_id']){
            $this->limit_sql .= " and app_id = ".$params['app_id'];
        }
        if($params['system'] == '1'){
            $this->limit_sql .= " and system NOT in('iOS','Android','Windows','Mac')";
        }elseif($params['system']){
            $this->limit_sql .= " and system = '".$params['system']."'";
        }
        if($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 7){
            if($params['channel'] && $params['user_code']){
                $this->limit_sql .= " and channel in ('".$params['channel']."','".$params['user_code']."')";
            }elseif($params['channel']){
                $this->limit_sql .= " and channel = '".$params['channel']."'";
            }elseif($params['user_code']){
                $this->limit_sql .= " and channel = '".$params['user_code']."'";
            }
            if($params['start_time'] && $params['end_time']){
                $this->limit_sql .= " and time >= ".strtotime($params['start_time'])." and time <= ".strtotime($params['end_time']);
            }elseif($params['start_time']){
                $this->limit_sql .= " and time >= ".strtotime($params['start_time']);
            }elseif($params['end_time']){
                $this->limit_sql .= " and time <= ".strtotime($params['end_time']);
            }
        }else{
            if($_SESSION['group_id'] == 10){
                $this->limit_sql .= " and channel = '".$_SESSION['user_code']."'";
            }
        }
        if($_SESSION['group_id'] != 1){
            $this->limit_sql .= " and app_id in (".$_SESSION['apps'].")";
        }
        $this->limit_sql .= " order by time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_super_h5_device($page,$params){
        $this->limit_sql = "select * from h5_super_stats_device where 1=1";
        if($params['app_id']){
            $this->limit_sql .= " and app_id = ".$params['app_id'];
        }
        if($params['system'] == '1'){
            $this->limit_sql .= " and system NOT in('iOS','Android','Windows','Mac')";
        }elseif($params['system']){
            $this->limit_sql .= " and system = '".$params['system']."'";
        }
        if($params['ch_code']){
            $this->limit_sql .= " and channel = '".$params['ch_code']."'";
        }
        if($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 7){
            if($params['channel'] && $params['ch_code']){
                $this->limit_sql .= " and channel in ('".$params['channel']."','".$params['ch_code']."')";
            }elseif($params['channel']){
                $this->limit_sql .= " and channel = '".$params['channel']."'";
            }elseif($params['ch_code']){
                $this->limit_sql .= " and channel = '".$params['ch_code']."'";
            }
            if($params['start_time'] && $params['end_time']){
                $this->limit_sql .= " and time >= ".strtotime($params['start_time'])." and time <= ".strtotime($params['end_time']);
            }elseif($params['start_time']){
                $this->limit_sql .= " and time >= ".strtotime($params['start_time']);
            }elseif($params['end_time']){
                $this->limit_sql .= " and time <= ".strtotime($params['end_time']);
            }
        }else{
            if($_SESSION['group_id'] == 10){
                $this->limit_sql .= " and channel = '".$_SESSION['user_code']."'";
            }
        }
        if($_SESSION['group_id'] != 1){
            $this->limit_sql .= " and app_id in (".$_SESSION['apps'].")";
        }
        $this->limit_sql .= " order by act_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_super_h5_role($page,$params){
        $this->limit_sql = "select * from h5_super_stats_user_app where 1=1";
        if($params['app_id']){
            $this->limit_sql .= " and AppID = ".$params['app_id'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and UserID = '".$params['user_id']."'";
        }
        if($params['ch_code']){
            $this->limit_sql .= " and Channel = '".$params['ch_code']."'";
        }
        if($params['ip']){
            $this->limit_sql .= " and ActIP = '".$params['ip']."'";
        }
        if($params['role_id']){
            $this->limit_sql .= " and RoleID = ".$params['role_id'];
        }
        if($params['area_server_id']){
            $this->limit_sql .= " and AreaServerID = ".$params['area_server_id'];
        }
        if($params['role_name']){
            $this->limit_sql .= " and RoleName = '".$params['role_name']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and ActTime >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and ActTime <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by ActTime desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_super_h5_login($page,$params){
        $this->limit_sql = "select * from h5_super_user_op_log where 1=1";
        if($params['app_id']){
            $this->limit_sql .= " and appid = ".$params['app_id'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and userid = '".$params['user_id']."'";
        }
        if($params['ch_code']){
            $this->limit_sql .= " and channel = '".$params['ch_code']."'";
        }
        if($params['ip']){
            $this->limit_sql .= " and ip = '".$params['ip']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and addtime >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and addtime <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by addtime desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_ch_all($app_id=""){
        $this->sql = "select channel,ch_code from channel_apps where 1 = 1";
        if($app_id){
            $this->sql .= " and super_id = ".$app_id;
        }
        $this->sql .= " group by ch_code";
        $this->doResultList();
        return $this->result;
    }

    public function get_rh_h5_apps(){
        $this->sql = "select * from super_apps where status = 1 and app_type = 3";
        $this->doResultList();
        return $this->result;
    }

    public function get_channel_info($ch){
        $data = $this->mmc->get("get_channel_info_h5_".$ch);
        if(!$data) {
            $this->sql = "select * from channel_apps where ch_code= ? ";
            $this->params = array($ch);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("get_channel_info_h5_".$ch, $data, 1, 3600);
        }
        return $data;
    }

    public function get_h5_device_num($app_id,$start_date,$end_date,$channels){
        $this->sql = "select count(distinct(sid)) as num from h5_super_stats_device where app_id = ".$app_id." and `time` >".strtotime($start_date)." and `time` <= ".strtotime($end_date);
        if($channels){
            $this->sql .= " and channel = '".$channels."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_h5_user_num($app_id,$start_date,$end_date,$channels){
        $this->sql = "select count(distinct(UserID)) as num from h5_super_stats_user_app where UserID != 0  and AppID = ".$app_id." and RegTime >".strtotime($start_date)." and RegTime <= ".strtotime($end_date);
        if($channels){
            $this->sql .= " and Channel = '".$channels."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_h5_role_num($app_id,$start_date,$end_date,$channels){
        $this->sql = "select count(distinct(RoleID)) as num from h5_super_stats_user_app where RoleID != 0 and AppID = ".$app_id." and RegTime >".strtotime($start_date)." and RegTime <= ".strtotime($end_date);
        if($channels){
            $this->sql .= " and Channel = '".$channels."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_h5_money_num($app_id,$start_date,$end_date,$channels){
        $this->sql = "select sum(a.pay_money) as money from super_orders as a inner join super_apps as b on a.app_id=b.app_id where a.status=2 and a.app_id = ".$app_id." and a.buy_time >".strtotime($start_date)." and a.buy_time <= ".strtotime($end_date);
        if($channels){
            $this->sql .= " and a.channel = '".$channels."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_h5_app_info($app_id){
        $this->sql = "select * from super_apps where status = 1 and app_type = 3 and app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_h5_pay_person_num($app_id,$start_date,$end_date,$channel){
        $this->sql = "select count(distinct(a.buyer_id)) as num from super_orders as a inner join super_apps as b on a.app_id=b.app_id where a.status=2 and a.app_id = ".$app_id." and a.buy_time >".strtotime($start_date)." and a.buy_time <= ".strtotime($end_date);
        if($channel){
            $this->sql .= " and a.channel = '".$channel."'";
        }
        $this->doResult();
        return $this->result;
    }
}