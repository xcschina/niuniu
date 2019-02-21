<?php
COMMON('dao','randomUtils');
class account_dao extends dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_ip_count($ip,$today){
        $this->sql = "select count(1) as num from send_sms_log where ip=? and add_date= ?";
        $this->params = array($ip,$today);
        $this->doResult();
        return $this->result;
    }

    public function get_phone_count($phone,$today){
        $this->sql = "select count(1) as num from send_sms_log where mobile=? and add_date= ?";
        $this->params = array($phone,$today);
        $this->doResult();
        return $this->result;
    }

    public function add_send_sms_log($ip,$phone,$code='',$today,$HTTP_REFERER,$result){
        $this->sql = "insert into send_sms_log(ip,mobile,sms_code,add_date,http_referer,result,add_time) values (?,?,?,?,?,?,?)";
        $this->params = array($ip,$phone,$code,$today,$HTTP_REFERER,$result,time());
        $this->doInsert();
    }

    public function  get_user_by_mobile($mobile){
        $this->sql = "select * from user_info where  mobile=?";
        $this->params = array($mobile);
        $this->doResult();
        return $this->result;
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
        $login_name = chr(rand(97, 122)).chr(rand(97, 122)).$user_id;
        $user_id_md5 = md5((string)$user_id."user_login");
        $phone_md5 = md5((string)$mobile."user_login");
        $login_name_md5 = md5($login_name."user_login");
        $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>$password,"mobile"=>$mobile,"login_type"=>1,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"qq_id"=>$qq_id,"buy_mobile"=>$mobile,"reg_from"=>7);
        $this->redis->set($phone_md5,$user_id);
        $this->redis->set($login_name_md5,$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }

    public function get_user_by_userid($user_id){
        $this->sql = "select * from user_info where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_login_log($user_id='',$account='',$login_ip,$login_pwd='',$source='',$browser='',$desc='',$status){
        $this->sql = "insert into user_login_log(user_id,account,login_ip,login_pwd,source,browser,`desc`,status,create_time) values (?,?,?,?,?,?,?,?,?)";
        $this->params = array($user_id,$account,$login_ip,$login_pwd,$source,$browser,$desc,$status,strtotime("now"));
        $this->doInsert();
    }

    public function insert_modify($params,$user_id,$ip){
        $this->sql = "insert into niuniu.modify_pwd_log(user_id,`type`,ip,add_time,mobile,`desc`) values(?,?,?,?,?,?)";
        $this->params = array($user_id,$params['type'],$ip,time(),$params['mobile'],$params['desc']);
        $this->doInsert();
    }

    public function insert_operation_log($user_id,$op_type,$op_results,$op_desc){
        $guid = randomUtils::guid();
        $this->sql = "insert into user_operation_log(guid,user_id,op_type,op_results,op_desc,op_time) values (?,?,?,?,?,?)";
        $this->params = array($guid,$user_id,$op_type,$op_results,$op_desc,strtotime("now"));
        $this->doInsert();
    }

    public function update_user_psw($password,$mobile,$user_id,$token){
        $guid = randomUtils::guid();
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
        $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
        $user_msg = array_merge($user_msg,array("guid"=>$guid));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_msg);
        $this->redis->lPush("user_login",$user_id_md5);
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
        $user_info = array_merge($user_info,array("id_number"=>$params['id_number'],"user_name"=>$params['user_name']));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function update_user_msg($params,$user_id){
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
        $user_info = array_merge($user_info,array("nick_name"=>$params['nick_name']));
        $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
        $user_msg = array_merge($user_msg,array("birthday"=>$params['birthday'],"sex"=>$params['sex'],"address"=>$params['address']));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_msg);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function get_user_by_nickname($nickname,$user_id){
        $this->sql="select count(1) as num from user_info where nick_name=? and user_id<>?";
        $this->params=array($nickname,$user_id);
        $this->doResult();
        $data=$this->result;
        return $data['num'];
    }

}