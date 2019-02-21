<?php
COMMON('niuniuDao','randomUtils');
class game_super_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }


    public function get_user_app_info($app_id,$user_id,$server_id,$role_id){
        $this->sql = "select * from h5_super_stats_user_app where AppID=? and UserID=? and AreaServerID=? and RoleID=?";
        $this->params = array($app_id,$user_id,$server_id,$role_id);
        $this->doResult();
        return $this->result;
    }

    public function add_user_app_info($params,$ip){
        $this->sql= "insert into h5_super_stats_user_app(ActIP,ActTime,AppID,AreaServerID,AreaServerName,Channel,GUID,
                    LastChannel,RegIP,RegTime,RoleID,RoleLevel,RoleName,UserID,SID,SdkVer,BroswerVer,Browser,Lang,OsVer,System,DeviceType)
                    values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($ip,time(),$params['appId'],$params['serverId'],$params['serverName'],$params['platform'],$params['token'],
            $params['platform'],$ip,time(),$params['roleId'],$params['roleLevel'],$params['roleName'],$params['userId'],
            $params['sid'],$params['sdkVer'],$params['broswerVer'],$params['browser'],$params['lang'],$params['osVer'],$params['system'],$params['deviceType']);
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function update_user_app_info($params,$ip,$id){
        $this->sql= "update h5_super_stats_user_app set ActTime=?,ActIP=?,RoleLevel=?,GUID=?,LastChannel=?,RoleName=? where ID=?";
        $this->params = array(time(),$ip,$params['roleLevel'],$params['token'],$params['platform'],$params['roleName'],$id);
        $this->doExecute();
    }

    public function get_device_info($sid,$channel,$app_id){
        $this->sql = "select * from h5_super_stats_device where app_id=? and channel=? and sid=? ";
        $this->params = array($app_id,$channel,$sid);
        $this->doResult();
        return $this->result;
    }

    public function update_device_info($params,$ip,$token,$id){
        $this->sql= "update h5_super_stats_device set act_time=?,act_ip=?,user_id=?,guid=? where id=?";
        $this->params = array(time(),$ip,$params['userId'],$token,$id);
        $this->doExecute();
    }

    public function add_device_log($params,$ip,$token){
        $this->sql= "insert into h5_super_stats_device(sid,app_id,user_id,channel,sdk_ver,broswer_ver,browser,lang,os_ver,system,device_type,ip,`time`,guid,act_time) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['sid'],$params['appId'],$params['userId'],$params['platform'],$params['sdkVer'],$params['broswerVer'],$params['browser'],$params['lang'],$params['osVer'],$params['system'],$params['deviceType'],$ip,time(),$token,time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function add_super_user_log($params,$ip,$do){
        $this->sql= "insert into h5_super_user_op_log(sid,sdk_ver,broswer_ver,browser,lang,os_ver,system,device_type,appid,channel,do,ip,token,userid,addtime) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
//        $this->sql= "insert into h5_super_user_op_log".date('Ym',time())."(sid,sdk_ver,broswer_ver,browser,lang,os_ver,system,device_type,appid,channel,do,ip,token,userid,addtime) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['sid'],$params['sdkVer'],$params['broswerVer'],$params['browser'],$params['lang'],$params['osVer'],$params['system'],$params['deviceType'],$params['appId'],$params['platform'],$do,$ip,$params['token'],$params['userId'],time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function get_super_info($super_app_id,$ch_app_id){
        $this->sql = "select * from channel_apps where super_id=? and app_id=? ";
        $this->params = array($super_app_id,$ch_app_id);
        $this->doResult();
        return $this->result;
    }
}

?>
