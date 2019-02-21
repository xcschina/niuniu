<?php
COMMON('niuniuDao','randomUtils');
class user_app_dao extends niuniuDao{
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_stats_list($params,$time){
        $this->sql = "SELECT st.*, u.mobile,u.from_app_id,u.reg_time AS u_reg_time FROM stats_user_app AS st LEFT JOIN `66173`.user_info AS u ON st.UserID = u.user_id WHERE 1=1";
        if($params['app_id']){
            $this->sql .= " AND st.AppID = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " AND st.Channel like '".$params['channel']."%'";
        }
        if($params['start_time']){
            $this->sql .= " AND st.RegTime >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " AND st.RegTime < ".strtotime($params['end_time']);
        }
        if(!$params['start_time'] && !$params['end_time']){
            $this->sql .= " AND st.RegTime >= ".$time;
        }
        $this->sql .= " GROUP BY RoleID ORDER BY st.AreaServerID DESC,st.RoleLevel DESC";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_list(){
        $this->sql = "select * from apps where status=1";
        $this->doResultList();
        return $this->result;
    }

    public function get_extension_list(){
        $this->sql = " select * from admins where group_id=12 and user_code !='' and is_del=0 and user_code is not null";
        $this->doResultList();
        return $this->result;
    }
}