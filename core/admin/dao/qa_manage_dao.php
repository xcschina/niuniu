<?php
COMMON('niuniuDao');
class qa_manage_dao extends niuniuDao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
    }

    public function get_ios_black_list($page,$params){
        $this->limit_sql = "select * from black_device  WHERE 1=1";
        if($params['sid']){
            $this->limit_sql .= " and sid = '".$params['sid']."'";
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_ios_black_info($id){
        $this->sql = "select * from black_device WHERE id = ?";
        $this->params= array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_black_info($data){
        $this->sql = "update black_device set is_del = ? where id= ?";
        $this->params= array($data['is_del'],$data['id']);
        $this->doExecute();
    }

    public function get_mmc_info($mmc){
        var_dump($this->mmc->get($mmc));
    }

    public function get_redis_info($redis,$num){
        if($num){
            $this->redis->select($num);
        }
        var_dump($this->redis->get($redis));
    }

    public function get_this_info(){
        $this->sql = "select  AreaServerID,AreaServerName,RoleID,RoleLevel,RoleName,UserID,RecordTime from  ios_stats_user_login_log201807  WHERE  AppID =1095  GROUP BY RoleID limit 400,400";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from  `66173`.user_info  WHERE  user_id =?";
        $this->params= array($user_id);
        $this->doResult();
        return $this->result;
    }
}