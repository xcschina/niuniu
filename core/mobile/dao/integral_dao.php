<?php
COMMON('dao','randomUtils');
class integral_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_user_by_mobile($mobile){
        $this->sql = "select * from user_info where mobile=?";
        $this->params = array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function insert_login_log($user_id='',$account='',$login_ip,$login_pwd='',$source='',$browser='',$desc='',$status){
        $this->sql="insert into user_login_log(user_id,account,login_ip,login_pwd,source,browser,`desc`,status,create_time) values (?,?,?,?,?,?,?,?,?)";
        $this->params=array($user_id,$account,$login_ip,$login_pwd,$source,$browser,$desc,$status,strtotime("now"));
        $this->doInsert();
    }

    public function insert_user_info($chr,$mobile, $reg_ip, $qq_id=''){
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
        $login_name = $chr.(string)$user_id;
        $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>"","mobile"=>$mobile,"login_type"=>1,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"qq_id"=>$qq_id,"buy_mobile"=>$mobile,"reg_from"=>2);
        $this->redis->set($phone_md5,$user_id);
        $this->redis->set(md5((string)$login_name."user_login"),$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }

    public function get_user_apptb($game_id,$user_id){
        $user_app_md5 = md5((string)$game_id.(string)$user_id."user_apptb");
        $user_app = $this->redis->hGetAll($user_app_md5);
        if (!$user_app){
            $this->sql = "select * from `niuniu`.user_apptb where app_id=? and userid=?";
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

    public function update_user_apptb($params,$token,$ip,$channel){
        $user_app_md5 = md5((string)$params['app_id'].(string)$params['userid']."user_apptb");
        $user_app = $this->redis->hGetAll($user_app_md5);
        if (!$user_app){
            $this->sql = "select * from `niuniu`.user_apptb where app_id=? and userid=?";
            $this->params = array($params['app_id'],$params['userid']);
            $this->doResult();
            $user_app = $this->result;
        }
        $data = array_merge($user_app,array("last_ip"=>$ip,"acttime"=>time(),"last_channel"=>$channel,"guid"=>$token));
        $this->redis->hMset($user_app_md5,$data);
        $this->redis->lPush("user_app",$user_app_md5);
    }

    public function get_user_info($user_id){
        $this->sql = "select * from user_info where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_user_info($params,$user_id){
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
        if ($user_info['mobile']) $this->redis->delete(md5((string)$user_info['mobile']."user_login"));
        $this->redis->set(md5((string)$params['mobile']."user_login"),$user_id);
        $user_info = array_merge($user_info,array("mobile"=>$params['mobile']));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function get_app_info($app_id){
        $data = memcache_get($this->mmc, 'h5_app_info'.$app_id);
        if(!$data){
            $this->sql = "select * from niuniu.apps where app_id=? ";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc,'h5_app_info'.$app_id, $data);
        }
        return $data;
    }

    public function get_goods_list($app_id){
        $this->sql = "select a.*,b.app_icon from niuniu.app_goods a left join niuniu.apps b on a.app_id = b.app_id where a.status=1 and a.app_id = ? order by a.good_price asc limit 4";
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_goods_info($goods_id){
        $this->sql = "select a.*,b.nnb_scale from niuniu.app_goods a left join niuniu.apps b on a.app_id = b.app_id where a.id = ?";
        $this->params = array($goods_id);
        $this->doResult();
        return $this->result;
    }

    public function update_usr_token($user_id,$token){
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
        $user_info = array_merge($user_info,array("token"=>$token));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }


    public function add_device_log($params,$ip,$token){
        $this->sql= "insert into niuniu.h5_stats_device(app_id,user_id,channel,sdk_ver,broswer_ver,browser,lang,os_ver,system,device_type,ip,`time`,guid) values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['user_id'],$params['channel'],$params['sdk_ver'],$params['broswer_ver'],$params['browser'],$params['lang'],$params['os_ver'],$params['system'],$params['deviceType'],$ip,time(),$token);
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function add_user_log($params,$ip,$do){
        $this->sql= "insert into niuniu.ios_user_op_log".date('Ym',time())."(appid,channel,do,ip,token,userid,addtime) values(?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['channel'],$do,$ip,$params['token'],$params['user_id'],time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

}