<?php
COMMON('niuniuDao');
class script_dao extends niuniuDao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_data($app_id,$channel,$date){
        $this->sql = "select sid,count(distinct userid) as user_count from stats_user_op_log".$date." where appid=? and channel = ? and addtime>=? group by sid";
        $this->params = array($app_id,$channel, strtotime(date('Y-m-d', time())));
        $this->doResultList();
        return $this->result;
    }

    public function get_device_info($device_no){
        $data = memcache_get($this->mmc, "blcak_sid_".md5($device_no));
        if (!$data) {
            $this->sql = "select * from device_black where device_no=? ";
            $this->params = array($device_no);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "blcak_sid_".md5($device_no), $data, 1, 600);
        }
        return $data;
    }

    public function insert_device_black($device_no){
        $this->sql = 'insert into device_black(device_no,device_type,device_status,add_time)values(?,?,?,?)';
        $this->params = array($device_no,1,1,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_device_operation_log($params){
        $this->sql = 'INSERT INTO device_black_log(device_black_id,operation_type,operation_id,operation_time)VALUES(?,?,?,?)';
        $this->params = array($params['device_black_id'],$params['operation_type'],$params['operation_id'],$params['operation_time']);
        $this->doInsert();
    }

    public function update_device_black($params){
        $this->sql = 'UPDATE device_black SET device_status=? ,add_time=? WHERE id=?';
        $this->params = array($params['device_status'],$params['add_time'],$params['id']);
        $this->doExecute();
    }

    public function get_reg_data($app_id,$channel,$start_time,$end_time){
        $this->sql = "select UserID,AppID,RegTime,ActTime,Channel from stats_user_app where AppID=? and Channel=? and RegTime >? and RegTime < ? group by UserID order by RegTime asc";
        $this->params = array($app_id,$channel,$start_time,$end_time);
        $this->doResultList();
        return $this->result;
    }

    public function get_data_list($reg_time){
        $this->sql = "select * from stats_user_app_retention where reg_time > ?";
        $this->params = array($reg_time);
        $this->doResultList();
        return $this->result;
    }

    public function get_user_app_info($params){
        $this->sql = "select UserID,AppID,RegTime,ActTime,Channel from stats_user_app where AppID=? and Channel=? and UserID=? order by ActTime desc";
        $this->params = array($params['app_id'],$params['channel'],$params['user_id']);
        $this->doResult();
        return $this->result;
    }

    public function update_retention($params,$day){
        $this->sql = "update stats_user_app_retention set day_".$day."=?,reg_time=? where app_id=? and user_id=? and channel=?";
        $this->params = array($params['ActTime'],$params['ActTime'],$params['AppID'],$params['UserID'],$params['Channel']);
        $this->doExecute();
    }

    public function insert_retention($params){
        $this->sql = "insert into stats_user_app_retention(user_id,app_id,reg_time,act_time,day_1,add_time,channel)values(?,?,?,?,?,?,?)";
        $this->params = array($params['UserID'],$params['AppID'],$params['RegTime'],$params['ActTime'],$params['RegTime'],time(),$params['Channel']);
        $this->doInsert();
    }

    public function get_user_retention_info($params){
        $this->sql = "select user_id from stats_user_app_retention where app_id=? and channel=? and user_id=?";
        $this->params = array($params['app_id'],$params['channel'],$params['user_id']);
        $this->doResult();
        return $this->result;
    }

//    public function get_redis_data($name){
//        return $this->redis->get($name);
//    }
//
//    public function set_redis_data($name,$data){
//        $this->redis->set($name,$data);
//    }
}