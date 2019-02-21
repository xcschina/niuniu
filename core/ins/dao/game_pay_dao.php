<?php
COMMON('niuniuDao','randomUtils');
class game_pay_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_game_info($id){
//        $data = memcache_delete($this->mmc, "web_app_info".$id);
//        $data = memcache_get($this->mmc, "web_app_info".$id);
        if (!$data) {
            $this->sql = "select * from apps where app_id=?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_info".$id, $data, 1, 600);
        }
        return $data;
    }

    public function get_exchanges($game_id){
//        memcache_delete($this->mmc, "web_app_exchanges".$game_id);
        $data = memcache_get($this->mmc, "web_app_exchanges".$game_id);
        if (!$data) {
//            $this->sql = "select * from app_goods where app_id=? and status = '1' ";
            $this->sql = "select * from app_goods where app_id=? and status = '1' and rec_type = '1' ";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_exchanges".$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_exchanges_by_goodid($game_id,$goodid){
        memcache_delete($this->mmc, "web_app_exchanges".$game_id."_".$goodid);
        $data = memcache_get($this->mmc, "web_app_exchanges".$game_id."_".$goodid);
        if (!$data) {
            $this->sql = "select * from app_goods where app_id=? and id=?";
            $this->params = array($game_id,$goodid);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_exchanges".$game_id."_".$goodid, $data, 1, 600);
        }
        return $data;
    }

    public function get_money($money_id,$app_id){
        $data = memcache_get($this->mmc, "web_app_money_".$money_id."_".$app_id);
        if (!$data) {
            $this->sql = "select * from app_goods where good_code=? and app_id=?";
            $this->params = array($money_id,$app_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_money_".$money_id."_".$app_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_money_info($app_id,$good_code){
//        $data = memcache_get($this->mmc, "web_app_money".$app_id.'_'.$good_code);
        if (!$data) {
            $this->sql = "select * from app_goods where app_id=? and good_code=?";
            $this->params = array($app_id,$good_code);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "web_app_money".$app_id.'_'.$good_code, $data, 1, 600);
        }
        return $data;
    }

    public function create_order($order){
        $this->sql = "insert into orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount, 
                                        pay_money,status, buy_time, ip, serv_id, channel,payExpandData,pay_from,`mac`,idfa,idfv,web_channel)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_order_num($params){
        $this->sql = "select count(*) as num from orders where role_id=? and serv_id = ? and product_id = ? and app_id = ? and status = ?";
        $this->params = array($params['player_id'],$params['serv_id'],$params['money_id'],$params['game_id'],2);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($order_id){
        $this->sql = "select * from orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($user_id){
        $user_id_md5 = md5((string)$user_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "select * from `66173`.user_info where user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if ($user_info){
                $user_msg = array("user_id"=>$user_info['user_id'],"m_verified"=>$user_info['m_verified'],"reg_time"=>$user_info['reg_time'],"wx_id"=>$user_info['wx_id'],"hn_wx_id"=>$user_info['hn_wx_id']);
                $user_info = array("user_id"=>$user_info['user_id'],"nick_name"=>$user_info['nick_name'],"login_name"=>$user_info['login_name'],
                    "user_name"=>$user_info['user_name'],"id_number"=>$user_info['id_number'],"password"=>$user_info['password'],
                    "mobile"=>$user_info['mobile'],"age"=>$user_info['age'],"login_type"=>$user_info['login_type'],"token"=>$user_info['token']);
                $this->redis->hMset($user_id_md5,$user_info);
                $this->redis->hMset(md5((string)$user_id."user_info"),$user_msg);
                $user_info = array_merge($user_info,$user_msg);
            }else{
                $user_info = array();
            }
        }else{
            $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
            $user_info = array_merge($user_info,$user_msg);
        }
        return $user_info;
    }

    public function update_user_psw($password,$mobile,$user_id,$token){
        $user_id_md5 = md5((string)$user_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($user_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        $user_info = array_merge($user_info,array("password"=>$password,"token"=>$token));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        $this->mmc->delete('user_info'.$user_id);
        $this->mmc->delete('user_info_'.$user_id);
    }

    public function insert_user_info($password, $mobile, $reg_ip, $qq_id=''){
        $guid = randomUtils::guid();
        if ($this->redis->exists("incrhash_user") == 1){
            $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
        }else{
            $this->sql = "SELECT user_id FROM user_info ORDER BY user_id DESC LIMIT 1";
            $this->doResult();
            $user_info_id = $this->result;
            if ($this->redis->exists("incrhash_user") == 1){
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
            }elseif ($user_info_id['user_id']){
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",(int)$user_info_id['user_id']+1000);
            }else{
                $user_id = $this->redis->hIncrBy("incrhash_user","user_id",1);
            }
        }
        $user_id_md5 = md5((string)$user_id."user_login");
        $phone_md5 = md5((string)$mobile."user_login");
        if($qq_id<>""){
            $login_name = 'n'.(string)$user_id;
            $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>$password,"mobile"=>$mobile,"login_type"=>1,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        }else{
            $user_login = array("user_id"=>$user_id,"login_name"=>$mobile,"user_name"=>"","password"=>$password,"mobile"=>$mobile,"login_type"=>1,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        }
        if(!empty($_SESSION['promoter_id'])){
            $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"qq_id"=>$qq_id,"buy_mobile"=>$mobile,"code"=>$_SESSION['promoter_id'],"m_verified"=>1);
        }else{
            $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"qq_id"=>$qq_id,"buy_mobile"=>$mobile,"m_verified"=>1);
        }
        $this->redis->set($phone_md5,$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }


    public function get_user_by_mobile($mobile){
        $phone_id_md5 = md5((string)$mobile."user_login");
        $user_id = $this->redis->get($phone_id_md5);
        if (!$user_id){
            $this->sql = "select * from `66173`.user_info where mobile=?";
            $this->params = array($mobile);
            $this->doResult();
            $user_info = $this->result;
            if ($user_info){
                $user_msg = array("user_id"=>$user_info['user_id'],"m_verified"=>$user_info['m_verified'],"reg_time"=>$user_info['reg_time'],"wx_id"=>$user_info['wx_id'],"hn_wx_id"=>$user_info['hn_wx_id']);
                $user_info = array("user_id"=>$user_info['user_id'],"nick_name"=>$user_info['nick_name'],"login_name"=>$user_info['login_name'],
                    "user_name"=>$user_info['user_name'],"id_number"=>$user_info['id_number'],"password"=>$user_info['password'],
                    "mobile"=>$user_info['mobile'],"age"=>$user_info['age'],"login_type"=>$user_info['login_type'],"token"=>$user_info['token']);
                $this->redis->set(md5((string)$mobile."user_login"),$user_info['user_id']);
                if ($user_info['login_name'] && $user_info['login_name']!=$mobile){
                    $this->redis->set(md5((string)$user_info['login_name']."user_login"),$user_info['user_id']);
                }
                $this->redis->hMset(md5((string)$user_info['user_id']."user_login"),$user_info);
                $this->redis->hMset(md5((string)$user_info['user_id']."user_info"),$user_msg);
                $user_info = array_merge($user_info,$user_msg);
            }else{
                $user_info = array();
            }
        }else{
            $user_info = $this->redis->hGetAll(md5((string)$user_id."user_login"));
            if (!$user_info){
                $this->sql = "select * from `66173`.user_info where mobile=?";
                $this->params = array($mobile);
                $user_info = $this->doResult();
                if ($user_info){
                    $user_msg = array("user_id"=>$user_info['user_id'],"m_verified"=>$user_info['m_verified'],"reg_time"=>$user_info['reg_time'],"wx_id"=>$user_info['wx_id'],"hn_wx_id"=>$user_info['hn_wx_id']);
                    $user_info = array("user_id"=>$user_info['user_id'],"nick_name"=>$user_info['nick_name'],"login_name"=>$user_info['login_name'],
                        "user_name"=>$user_info['user_name'],"id_number"=>$user_info['id_number'],"password"=>$user_info['password'],
                        "mobile"=>$user_info['mobile'],"age"=>$user_info['age'],"login_type"=>$user_info['login_type'],"token"=>$user_info['token']);
                    if ($user_info['login_name'] && $user_info['login_name']!=$mobile){
                        $this->redis->set(md5((string)$user_info['login_name']."user_login"),$user_info['user_id']);
                    }
                    $this->redis->hMset(md5((string)$user_info['user_id']."user_login"),$user_info);
                    $this->redis->hMset(md5((string)$user_info['user_id']."user_info"),$user_msg);
                    $user_info = array_merge($user_info,$user_msg);
                }else{
                    $user_info = array();
                }
            }else{
                $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
                $user_info = array_merge($user_info,$user_msg);
            }
        }
        return $user_info;
    }

    public function update_user_molie($user_id,$mobile){
        $user_id_md5 = md5((string)$user_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM `66173`.user_login WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM `66173`.user_info WHERE user_id=?";
                $this->params = array($user_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        if ($user_info['mobile']) $this->redis->delete(md5((string)$user_info['mobile']."user_login"));
        $this->redis->set(md5((string)$mobile."user_login"),$user_id);
        $user_info = array_merge($user_info,array("mobile"=>$mobile));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function get_h5_game_info($app_id){
        $this->sql = "select * from apps where app_id=?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_apptb($game_id,$user_id){
        $user_app_md5 = md5((string)$game_id.(string)$user_id."user_apptb");
        $user_app = $this->redis->hGetAll($user_app_md5);
        if (!$user_app){
            $this->sql = "select * from user_apptb where app_id=? and userid=?";
            $this->params = array($game_id,$user_id);
            $this->doResult();
            $user_app = $this->result;
            if ($user_app){
                $data = array("app_id"=>$user_app['app_id'],"userid"=>$user_app['userid'],"ip"=>$user_app['ip'],"last_ip"=>$user_app['last_ip'],
                    "guid"=>$user_app['guid'], "channel"=>$user_app['channel'],"last_channel"=>$user_app['last_channel'],"usertype"=>$user_app['usertype'],
                    "acttime"=>$user_app['acttime'],"regtime"=>$user_app['regtime']);
                $this->redis->hMset($user_app_md5,$data);
            }
        }
        return $user_app;
    }

    public function add_user_apptb($user_id,$game_id,$token,$ip,$channel){
        $data = array("app_id"=>$game_id,"userid"=>$user_id,"ip"=>$ip,"last_ip"=>$ip,"guid"=>$token,
                        "channel"=>$channel,"last_channel"=>$channel,"usertype"=>0,"acttime"=>time(),"regtime"=>time());
        $this->redis->hMset(md5((string)$game_id.(string)$user_id."user_apptb"),$data);
        $this->redis->lPush("user_app",md5((string)$game_id.(string)$user_id."user_apptb"));
    }

    public function add_user_log($params,$ip,$do){
        $this->sql= "insert into ios_user_op_log".date('Ym',time())."(appid,channel,do,ip,token,userid,addtime) values(?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['channel'],$do,$ip,$params['token'],$params['user_id'],time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function update_user_apptb($token,$ip,$channel,$params){
        $user_app_md5 = md5((string)$params['app_id'].(string)$params['userid']."user_apptb");
        $user_app = $this->redis->hGetAll($user_app_md5);
        if (!$user_app){
            $this->sql = "select * from user_apptb where app_id=? and userid=?";
            $this->params = array($params['app_id'],$params['userid']);
            $this->doResult();
            $user_app = $this->result;
        }
        $data = array_merge($user_app,array("last_ip"=>$ip,"acttime"=>time(),"last_channel"=>$channel,"guid"=>$token));
        $this->redis->hMset($user_app_md5,$data);
        $this->redis->lPush("user_app",$user_app_md5);
    }

    public function get_user_app_info($app_id,$user_id,$server_id,$role_id){
        $this->sql = "select * from ios_stats_user_app where AppID=? and UserID=? and AreaServerID=? and RoleID=?";
        $this->params = array($app_id,$user_id,$server_id,$role_id);
        $this->doResult();
        return $this->result;
    }
    public function add_user_app_info($params,$ip){
        $this->sql= "insert into ios_stats_user_app(ActIP,ActTime,AppID,AreaServerID,AreaServerName,Channel,GUID,
                    LastChannel,RegIP,RegTime,RoleID,RoleLevel,RoleName,UserID)
                    values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($ip,time(),$params['app_id'],$params['server_id'],$params['server_name'],$params['channel'],$params['token'],
                            $params['channel'],$ip,time(),$params['role_id'],$params['role_level'],$params['role_name'],$params['user_id']);
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function add_device_log($params,$ip,$token){
        $this->sql= "insert into h5_stats_device(app_id,user_id,channel,sdk_ver,broswer_ver,browser,lang,os_ver,system,device_type,ip,`time`,guid) values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['user_id'],$params['channel'],$params['sdk_ver'],$params['broswer_ver'],$params['browser'],$params['lang'],$params['os_ver'],$params['system'],$params['deviceType'],$ip,time(),$token);
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function update_user_app_info($params,$ip,$id){
        $this->sql= "update ios_stats_user_app set ActTime=?,ActIP=?,RoleLevel=?,GUID=?,LastChannel=?,RoleName=? where ID=?";
        $this->params = array(time(),$ip,$params['role_level'],$params['token'],$params['channel'],$params['role_name'],$id);
        $this->doExecute();
    }

    public function get_user_pid($pid){
        $data = memcache_delete($this->mmc, 'h5_game_info'.$pid);
        $data = memcache_get($this->mmc, 'h5_game_info'.$pid);
        if(!$data){
            $this->sql = "select * from `niuniu`.app_ios_pack where apple_id=? ";
            $this->params = array($pid);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,'h5_game_info'.$pid, $data);
        }
        return $data;
    }

    public function update_usr_token($user_id,$token){
        $user_id_md5 = md5((string)$user_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM `66173`.user_login WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM `66173`.user_info WHERE user_id=?";
                $this->params = array($user_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        $user_info = array_merge($user_info,array("token"=>$token));
        memcache_delete($this->mmc,'user_info_'.$user_id);
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }


}

?>
