<?php
COMMON('dao','randomUtils');
class account_dao extends dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
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
//        $guid = randomUtils::guid();
//        $this->sql = "insert into user_info(guid,password,mobile,login_name,reg_time,reg_ip,qq_id,buy_mobile,reg_from,token,login_type)values(?,?,?,?,?,?,?,?,?,?,?)";
//        $this->params = array($guid,$password, $mobile, $mobile, strtotime("now"), $reg_ip, $qq_id,$mobile,7, $guid,1);
//        $this->doInsert();
//        $id = $this->LAST_INSERT_ID;
//
//        $login_name = chr(rand(97, 122)).chr(rand(97, 122)).$id;
//        $this->sql = "update user_info set login_name=? where user_id=?";
//        $this->params = array($login_name,$id);
//        $this->doExecute();
//        return $id;
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
//        $guid = randomUtils::guid();
//        $this->sql = "update user_info set password=?,`token`=?,guid=? where mobile=? and user_id=?";
//        $this->params = array($password,$token,$guid,$mobile,$user_id);
//        $this->doExecute();
    }

    public function update_user_info($params,$user_id){
//        $this->sql = "update user_info set id_number = ?,user_name = ? where user_id = ?";
//        $this->params = array($params['id_number'],$params['user_name'],$user_id);
//        $this->doExecute();
    }

    public function update_user_msg($params,$user_id){
//        $this->sql = "update user_info set nick_name = ?,birthday = ?,sex = ?,address = ? where user_id = ?";
//        $this->params = array($params['nick_name'],$params['birthday'],$params['sex'],$params['address'],$user_id);
//        $this->doExecute();
    }

    public function get_user_by_nickname($nickname,$user_id){
        $this->sql="select count(1) as num from user_info where nick_name=? and user_id<>?";
        $this->params=array($nickname,$user_id);
        $this->doResult();
        $data=$this->result;
        return $data['num'];
    }

}