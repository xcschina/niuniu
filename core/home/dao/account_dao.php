<?php
COMMON('dao','randomUtils');
class account_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_user_by_mobile($mobile){
        $this->sql="select * from user_info where mobile=? or nick_name=?";
        $this->params=array($mobile,$mobile);
        $this->doResult();
        return $this->result;
    }

    public function get_user_by_email($email){
        $this->sql="select * from user_info where  email=?";
        $this->params=array($email);
        $this->doResult();
        return $this->result;
    }

    public function get_user_by_userid($user_id){
        $this->sql="select * from user_info where  user_id=?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_by_nickname($nickname,$user_id){
        $this->sql="select count(1) as num from user_info where nick_name=? and user_id<>?";
        $this->params=array($nickname,$user_id);
        $this->doResult();
        $data=$this->result;
        return $data['num'];
    }

    public function get_user_by_qq_id($open_id){
        $this->sql = "select * from user_info where qq_id=?";
        $this->params = array($open_id);
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
        $user_id_md5 = md5((string)$user_id."user_login");
        $phone_md5 = md5((string)$mobile."user_login");
        if($qq_id<>""){
            $login_name = 'n'.(string)$user_id;
            $user_login = array("user_id"=>$user_id,"login_name"=>$login_name,"user_name"=>"","password"=>$password,"mobile"=>$mobile,"login_type"=>1,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        }else{
            $user_login = array("user_id"=>$user_id,"login_name"=>$mobile,"user_name"=>"","password"=>$password,"mobile"=>$mobile,"login_type"=>1,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        }
        if(!empty($_SESSION['promoter_id'])){
            $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"qq_id"=>$qq_id,"buy_mobile"=>$mobile,"code"=>$_SESSION['promoter_id']);
        }else{
            $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"qq_id"=>$qq_id,"buy_mobile"=>$mobile);
        }
        $this->redis->set($phone_md5,$user_id);
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
    }

    public function insert_guest_user_info($password, $reg_ip){
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
        $user_login = array("user_id"=>$user_id,"login_name"=>"","user_name"=>"","password"=>$password,"mobile"=>"","login_type"=>0,"token"=>$guid,"nick_name"=>"","id_number"=>"","age"=>0);
        $user_info = array("user_id"=>$user_id,"guid"=>$guid,"reg_time"=>strtotime("now"),"reg_ip"=>$reg_ip,"channel"=>"nnwl");
        $this->redis->hMset($user_id_md5,$user_login);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        return $user_id;
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
        $this->mmc->delete('user_info'.$_SESSION['user_id']);
        $this->mmc->delete('user_info_'.$_SESSION['user_id']);
    }

//    public function update_usr_info_popu($code,$user_id){
//        $this->sql="update user_info set code=? where user_id=?";
//        $this->params=array($code,$user_id);
//        $this->doExecute();
//    }

    public function update_user_phone($mobile,$user_id){
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
        $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
        if ($user_info['mobile']) $this->redis->delete(md5((string)$user_info['mobile']."user_login"));
        $this->redis->set(md5((string)$mobile."user_login"),$user_id);
        $user_info = array_merge($user_info,array("mobile"=>$mobile));
        $user_msg = array_merge($user_msg,array("buy_mobile"=>$mobile));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_msg);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function update_user_email($email,$user_id){
        $this->sql="update user_info set email=?,e_verified=1 where user_id=?";
        $this->params=array($email,$user_id);
        $this->doExecute();
    }

    public function update_usr_info($usr, $usr_id){
        $user_id_md5 = md5((string)$usr_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($usr_id);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($usr_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        $user_msg = $this->redis->hGetAll(md5((string)$usr_id."user_info"));
        $user_info = array_merge($user_info,array("nick_name"=>$usr['nick_name']));
        $user_msg = array_merge($user_msg,array("sex"=>$usr['sex'],"birthday"=>$usr['birthday']));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->hMset(md5((string)$usr_id."user_info"),$user_msg);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function update_guest_usr_info($usr, $usr_id){
        $user_id_md5 = md5((string)$usr_id."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($usr_id);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($usr_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        if ($user_info['login_name']) $this->redis->delete(md5((string)$user_info['login_name']."user_login"));
        $login_name_md5 = md5((string)$usr."user_login");
        $this->redis->set($login_name_md5,$usr_id);
        $user_info = array_merge($user_info,array("nick_name"=>$usr,"login_name"=>$usr));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function update_user_idcard($id_number, $user_name, $user_id){
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
        $user_info = array_merge($user_info,array("id_number"=>$id_number,"user_name"=>$user_name));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function insert_login_log($user_id='',$account='',$login_ip,$login_pwd='',$source='',$browser='',$desc='',$status){
        $this->sql="insert into user_login_log(user_id,account,login_ip,login_pwd,source,browser,`desc`,status,create_time) values (?,?,?,?,?,?,?,?,?)";
        $this->params=array($user_id,$account,$login_ip,$login_pwd,$source,$browser,$desc,$status,strtotime("now"));
        $this->doInsert();
    }

    public function insert_qq_login_log($user_id='',$account='',$login_ip,$qq_id='',$source='',$browser='',$desc='',$status){
        $this->sql="insert into user_login_log(user_id,account,login_ip,qq_id,source,browser,`desc`,status,create_time) values (?,?,?,?,?,?,?,?,?)";
        $this->params=array($user_id,$account,$login_ip,$qq_id,$source,$browser,$desc,$status,strtotime("now"));
        $this->doInsert();
    }

    public function get_login_log($user_id){
        $this->sql="select * from user_login_log where user_id=? and status='1' order by id desc limit 2";
        $this->params=array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_operation_log($user_id,$op_type,$op_results,$op_desc){
        $guid = randomUtils::guid();
        $this->sql="insert into user_operation_log(guid,user_id,op_type,op_results,op_desc,op_time) values (?,?,?,?,?,?)";
        $this->params=array($guid,$user_id,$op_type,$op_results,$op_desc,strtotime("now"));
        $this->doInsert();
    }

    public function get_promoter_code($code){
        $this->sql = "select * from promoter_tb where code = ?";
        $this->params = array($code);
        $this->doResult();
        return $this->result;
    }
}