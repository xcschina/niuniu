<?php
COMMON('niuniuDao','randomUtils');
class user_dao extends Dao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
        $this->TB_NAME = "admin_menus";
    }

    public function get_log_list($page,$params){
        $this->limit_sql = "select * from niuniu.operator_log where status <> 3";
        if($params['operator_id']){
            $this->limit_sql .= " and operator_id = ".$params['operator_id'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and user_id = ".$params['user_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and status = ".$params['status'];
        }
        if($params['mobile']){
            $this->limit_sql .= " and mobile = '".$params['mobile']."'";
        }
        $this->limit_sql .= " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from user_info where user_id=? ";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function get_user_mobile($mobile){
        $this->sql = "select user_id,nick_name,login_name,mobile from user_info where mobile = ? ";
        $this->params = array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function update_user_pwd($password,$user_id){
        $token = randomUtils::guid();
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
//        $this->sql = "update user_info set password = ?,token = ? where user_id = ?";
//        $this->params = array($password,$token,$user_id);
//        $this->doExecute();
    }

    public function insert_operation_log($operator_id,$params,$user_id,$ip){
        $this->sql = "insert into niuniu.operator_log (ip,operator_id,user_id,mobile,new_password,add_time,status,old_password) values(?,?,?,?,?,?,?,?)";
        $this->params = array($ip,$operator_id,$user_id,$params['mobile'],$params['password'],time(),'1',$params['old_password']);
        $this->doExecute();
    }

    public function update_user_mobile($user_id){
        $token = randomUtils::guid();
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
        $user_msg = $this->redis->hGetAll(md5((string)$user_id."user_info"));
        $user_info = array_merge($user_info,array("mobile"=>'',"token"=>$token,"login_type"=>0));
        $user_msg = array_merge($user_msg,array("m_verified"=>0));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->hMset(md5((string)$user_id."user_info"),$user_msg);
        $this->redis->lPush("user_login",$user_id_md5);
//        $this->sql = "update user_info set mobile = ?,token = ?,m_verified=?,login_type=? where user_id = ?";
//        $this->params = array('',$token,0,0,$user_id);
//        $this->doExecute();
    }

    public function insert_operation($params,$operator_id,$ip){
        $this->sql = "insert into niuniu.operator_log(ip,operator_id,mobile,user_id,status,add_time) values(?,?,?,?,?,?)";
        $this->params = array($ip,$operator_id,$params['mobile'],$params['user_id'],'2',time());
        $this->doExecute();
    }

    public function get_user_suspend_list($page,$params){
        $this->limit_sql = "SELECT s.id,s.user_id,s.mobile_old,u.mobile,s.statu,u.nick_name FROM user_suspend s LEFT JOIN user_info u ON s.user_id=u.user_id WHERE 1=1 ";
        if($params['user_id']){
            $this->limit_sql .= " AND s.user_id = ".$params['user_id'];
        }
        if($params['mobile']){
            $this->limit_sql .= " AND s.mobile_old = '".$params['mobile']."'";
        }
        $this->limit_sql .= " ORDER BY s.id DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_suspend_by_id($user_id){
        $this->sql = "SELECT u.user_id,u.mobile,u.nick_name,u.password,s.statu FROM user_info u LEFT JOIN user_suspend s ON u.user_id=s.user_id WHERE
                      u.user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_suspend_by_mobile($mobile){
        $this->sql = "SELECT u.user_id,u.mobile,u.nick_name,u.password,s.statu FROM user_info u LEFT JOIN user_suspend s ON u.user_id=s.user_id WHERE
                      u.mobile=?";
        $this->params = array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function update_user_suspend($params){
        $token = randomUtils::guid();
        $user_id_md5 = md5((string)$params['user_id']."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($params['user_id']);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($params['user_id']);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        if ($user_info['mobile']) $this->redis->delete(md5((string)$user_info['mobile']."user_login"));
        if ($params['mobile']){
            $user_info = array_merge($user_info,array("login_type"=>1,"mobile"=>$params['mobile'],"token"=>$token,"password"=>$params['password'],"nick_name"=>$params['mobile']));
        }else{
            $user_info = array_merge($user_info,array("mobile"=>$params['mobile'],"token"=>$token,"password"=>$params['password']));
        }
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
    }

    public function get_suspend_by_id($user_id){
        $this->sql = "SELECT * FROM user_suspend WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_suspend_info($params){
        $this->sql = "UPDATE user_suspend SET password_old=?,mobile_old=?,password_new=?,mobile_new=?,statu=? WHERE user_id=?";
        $this->params = array($params['password_old'],$params['mobile_old'],$params['password_new'],$params['mobile_new'],$params['statu'],$params['user_id']);
        $this->doExecute();
    }

    public function insert_suspend_info($params){
        $this->sql = "INSERT INTO user_suspend(user_id,password_old,mobile_old,password_new,mobile_new,statu)VALUES(?,?,?,?,?,?)";
        $this->params = array($params['user_id'],$params['password_old'],$params['mobile_old'],$params['password_new'],$params['mobile_new'],$params['statu']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_suspend_info_log($params){
        $this->sql = "INSERT INTO user_suspend_log(suspend_id,type,operate_time,operate_id)VALUES(?,?,?,?)";
        $this->params = array($params['id'],$params['type'],time(),$_SESSION['usr_id']);
        $this->doInsert();
    }

    public function del_account_mmc($login_name){
        $this->mmc->delete($this->mmc,"account_info".$login_name);
    }

    public function get_account_mmc($usr_info){
//        $user_id_md5 = md5((string)$usr_info['user_id']."user_login");
//        $user_info = $this->redis->hGetAll($user_id_md5);
//        return $user_info;
        return  $this->mmc->get("account_info".$usr_info['login_name']);
    }
}