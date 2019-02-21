<?php
// --------------------------------------
//  店铺系统 - 账号 <zbc> < 2016/4/14 >
//  从主站复制而来
// --------------------------------------
COMMON('dao','randomUtils');
class account_shop_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function  get_user_by_mobile($mobile){
        $this->sql="select * from user_info where  mobile=?";
        $this->params=array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function  get_user_by_email($email){
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

    public function  get_user_by_nickname($nickname,$user_id){
        $this->sql="select count(1) as num from user_info where nick_name=? and user_id<>?";
        $this->params=array($nickname,$user_id);
        $this->doResult();
        $data=$this->result;
        return $data['num'];
    }

    public function insert_user_info($password, $mobile, $reg_ip, $qq_id=''){
//        $guid = randomUtils::guid();
//        $this->sql= "insert into user_info(guid,password,mobile,reg_time,reg_ip,qq_id,buy_mobile,reg_from,token)values(?,?,?,?,?,?,?,?,?)";
//        $this->params = array($guid,$password, $mobile,strtotime("now"), $reg_ip, $qq_id,$mobile,2, $guid);
//        $this->doInsert();
//        $id = $this->LAST_INSERT_ID;
//        return $id;
    }

    public function update_user_psw($password,$mobile){
//        $this->sql="update user_info set password=? where mobile=?";
//        $this->params=array($password,$mobile);
//        $this->doExecute();
    }

    public function update_user_phone($mobile,$user_id){
//        $this->sql="update user_info set mobile=?,buy_mobile=? where user_id=?";
//        $this->params=array($mobile,$mobile,$user_id);
//        $this->doExecute();
    }

    public function update_user_email($email,$user_id){
//        $this->sql="update user_info set email=?,e_verified=1 where user_id=?";
//        $this->params=array($email,$user_id);
//        $this->doExecute();
    }

    public function update_usr_info($usr, $usr_id){
//        $this->sql = "update user_info set nick_name=?,sex=?,birthday=? where user_id=?";
//        $this->params = array($usr['nick_name'],$usr['sex'],$usr['birthday'],$usr_id);
//        $this->doExecute();
    }


    public function insert_login_log($user_id='',$account='',$login_ip,$login_pwd='',$source='',$browser='',$desc='',$status){
        $this->sql="insert into user_login_log(user_id,account,login_ip,login_pwd,source,browser,`desc`,status,create_time) values (?,?,?,?,?,?,?,?,?)";
        $this->params=array($user_id,$account,$login_ip,$login_pwd,$source,$browser,$desc,$status,strtotime("now"));
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

    public function get_user_by_qq_id($open_id){
        $this->sql = "select * from user_info where qq_id=?";
        $this->params = array($open_id);
        $this->doResult();
        return $this->result;
    }
}