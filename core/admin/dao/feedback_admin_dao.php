<?php
COMMON('dao');

class feedback_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "sys_feedbacktb";
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name from sys_feedbacktb as a inner join niuniu.apps as b on a.appid=b.app_id where 1=1 ";
        if($params['app_id']){
            $this->limit_sql = $this->limit_sql." and a.appid = ".$params['app_id'];
        }
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql." and a.user_id = ".$params['user_id'];
        }
        if($params['question_status'] && is_numeric($params['question_status']) || $params['question_status'] === '0'){
            $this->limit_sql = $this->limit_sql." and a.question_status = ".$params['question_status'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_feedback_name(){
        $this->sql = "select * from  `niuniu`.apps  order by id desc ";
        $this->doResultList();
        return $this->result;
    }

    public function get_account_retrieve($page,$params){
        $this->limit_sql = 'select * from sys_account_back where 1=1 ';
        if($params['mobile']){
            $this->limit_sql = $this->limit_sql." and mobile = ".$params['mobile'];
        }
        if($params['qq']){
            $this->limit_sql = $this->limit_sql." and qq = ".$params['qq'];
        }
        $this->limit_sql = $this->limit_sql." order by add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_account_info($id) {
        $this->sql='select * from sys_account_back where id='.$id;
        $this->doResult();
        return $this->result;
    }

    public function update_account($feedback,$id) {
        $this->sql = "update sys_account_back set status=?, operator_id=?, reply=?, remarks=?, operator_time=? where id=?";
        $this->params = array($feedback['status'],$feedback['operator_id'],$feedback['reply'],$feedback['remarks'],$feedback['operator_time'],$id);
        $this->doExecute();
    }

    public function get_feedback($id){
        $this->sql = 'select a.*,b.app_name,c.nick_name from sys_feedbacktb as a inner join niuniu.apps as b on a.appid=b.app_id left join user_info c on a.user_id = c.user_id where a.id=?';
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_feedback($params, $id){
        $this->sql = "update sys_feedbacktb set feedback=?,feedback_usr=?,feedback_time=?,is_reply = 1,read_status = 0 where id=?";
        $this->params = array($params['feedback'], $params['feedback_usr'], $params['feedback_time'], $id);
        $this->doExecute();

        $this->sql = "insert into `niuniu`.reply_feedback (pid,operator_id,`desc`,add_time) values (?,?,?,?)";
        $this->params = array($id,$params['feedback_usr'],$params['feedback'],time());
        $this->doInsert();
    }

    public function get_user_by_mobile($mobile){
        $this->sql = "select user_id,nick_name,login_name,mobile from user_info where mobile= ?";
        $this->params = array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function update_user_mobile($user_id,$mobile,$new_mobile){
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
        if ($user_info['mobile']==$mobile){
            if ($user_info['mobile']) $this->redis->delete(md5((string)$user_info['mobile']."user_login"));
            if ($new_mobile) $this->redis->set(md5((string)$new_mobile."user_login"),$user_id);
            $user_info = array_merge($user_info,array("mobile"=>$new_mobile));
            $this->redis->hMset($user_id_md5,$user_info);
            $this->redis->lPush("user_login",$user_id_md5);
        }
//        $this->sql = "update user_info set mobile=? WHERE user_id=? and mobile=?";
//        $this->params = array($new_mobile,$user_id,$mobile);
//        $this->doExecute();
    }

    public function add_operation_log($operation_id,$user_id,$mobile,$new_mobile,$desc,$type){
        $this->sql = "insert into change_mobile_log(operation_id,old_mobile,new_mobile,user_id,`desc`,add_time,type) VALUES(?,?,?,?,?,?,?)";
        $this->params = array($operation_id,$mobile,$new_mobile,$user_id,$desc,time(),$type);
        $this->doInsert();
        return $this->LAST_INSERT_ID;

    }

    public function get_change_mobile_log($page){
        $this->limit_sql = "select * from change_mobile_log where 1=1";
        $this->limit_sql = $this->limit_sql." order by add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
    //身份证查询用户信息
    public function get_user_idCard($userId,$userName,$userIdCard){
        $this->sql = "SELECT user_id,nick_name,login_name FROM user_info WHERE user_id=? AND user_name=? AND id_number=? LIMIT 1";
        $this->params = array($userId,$userName,$userIdCard);
        $this->doResult();
        return $this->result;
    }
    public function get_user_info($user_id){
        $this->sql = "SELECT user_id,nick_name,login_name FROM user_info WHERE user_id=? ";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    //身份证图片上传
    public function update_user_idCard($userId,$userName,$userIdCard,$newPhone){
        $user_id_md5 = md5((string)$userId."user_login");
        $user_info = $this->redis->hGetAll($user_id_md5);
        if (!$user_info){
            $this->sql = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($userId);
            $this->doResult();
            $user_info = $this->result;
            if (!$user_info){
                $this->sql = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($userId);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        if ($user_info['user_name']==$userName && $user_info['id_number']==$userIdCard){
            if ($user_info['mobile']) $this->redis->delete(md5((string)$user_info['mobile']."user_login"));
            if ($newPhone) $this->redis->set(md5((string)$newPhone."user_login"),$userId);
            $user_info = array_merge($user_info,array("mobile"=>$newPhone));
            $this->redis->hMset($user_id_md5,$user_info);
            $this->redis->lPush("user_login",$user_id_md5);
        }
//        $this->sql = "UPDATE user_info set mobile=? WHERE user_id=? AND user_name=? AND id_number=?";
//        $this->params = array($newPhone,$userId,$userName,$userIdCard);
//        $this->doExecute();
    }
    //身份证换绑记录
    public function add_operation_id_log($operation_id,$user_id,$id_card,$new_mobile,$desc,$img_path,$type){
        $this->sql = "insert into change_mobile_log(operation_id,id_number,new_mobile,user_id,`desc`,add_time,image,type) VALUES(?,?,?,?,?,?,?,?)";
        $this->params = array($operation_id,$id_card,$new_mobile,$user_id,$desc,time(),$img_path,$type);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    //更新用户密码
    public function update_user_pwd($password,$user_id){
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
        $user_info = array_merge($user_info,array("password"=>$password));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
//        $this->sql = "UPDATE user_info set password=? WHERE user_id=?";
//        $this->params = array($password,$user_id);
//        $this->doExecute();
    }

    public function add_admin_operation_log($operation_id,$desc,$remark){
        $this->sql = "insert into `niuniu`.admin_operation_log(operation_id,`desc`,remarks,add_time) VALUES(?,?,?,?)";
        $this->params = array($operation_id,$desc,$remark,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_feedback_list(){
        $this->sql = "select * from sys_feedbacktb  ";
        $this->doResultList();
        return $this->result;
    }

    public function get_reply_list($id){
        $this->sql = "select a.*,b.real_name,c.nick_name from `niuniu`.reply_feedback a left join admins b on a.operator_id = b.id left join user_info c on a.user_id = c.user_id  where pid = ? ";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_reply_feedback($params,$id){
        $this->sql = "insert into `niuniu`.reply_feedback (pid,operator_id,`desc`,add_time) values (?,?,?,?)";
        $this->params = array($id,$params['operator_id'],$params['desc'],time());
        $this->doInsert();

        $this->sql = "update sys_feedbacktb set is_reply = 1,read_status = 0,feedback_time=?,feedback_usr=? where id = ?";
        $this->params = array($params['add_time'],$params['operator_id'],$id);
        $this->doExecute();
    }

    public function update_feedback_reply($id){
        $this->sql = 'update sys_feedbacktb set question_status = 1 where id =?';
        $this->params = array($id);
        $this->doExecute();
    }
}
?>