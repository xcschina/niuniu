<?php
COMMON('dao','niuniuDao','randomUtils');
class web_account_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST,REDIS_PORT);
        $this->redis->select(2);
    }

    public function set_usr_session($key, $data){
        $session_data = $this->mmc->get("feedback-session-".session_id());
        $session_data[$key] = $data;
        $this->mmc->set("feedback-session-".session_id(), $session_data, 1,600);
    }

    public function get_nd_info($user_id,$app_id){
        $this->sql = "select nd_num,nd_lock from niuniu.nd_user_info where user_id = ? and app_id = ?";
        $this->params = array($user_id,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_nd_log($user_id,$app_id,$three_month){
        $data = $this->mmc->get("get_nd_log_".$user_id.'_'.$app_id);
        if(!$data){
            $this->sql = "select * from niuniu.nd_operation_log where user_id = ? and app_id = ? and do_type = ? and add_time >= ?";
            $this->params = array($user_id,$app_id,1,$three_month);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_nd_log_".$user_id."_".$app_id,$data,1,600);
        }
        return $data;
    }

    public function get_nd_orders($user_id,$app_id,$three_month){
        $data = $this->mmc->get('get_nd_orders_'.$user_id."_".$app_id);
        if(!$data){
            $this->sql = "select order_id as orders,pay_money as nd_num,pay_time as add_time,status as do_type from niuniu.orders where buyer_id = ? and app_id =? and pay_channel = ? and status = ? and pay_time >= ?";
            $this->params = array($user_id,$app_id,6,2,$three_month);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('get_nd_orders_'.$user_id."_".$app_id,$data,1,600);
        }
        return $data;
    }

    public function get_usr_session($key){
        $session_data = $this->mmc->get("feedback-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }

    public function order_list($user_id, $status = -1,$page){
        $data = $this->mmc->get("order_list_".$user_id);
        if(!$data){
            if($status == -1){
                $this->limit_sql = 'select * from orders where buyer_id=? order by buy_time DESC';
                $this->params = array($user_id);
            }else{
                $this->limit_sql = 'select * from orders where user_id=? and status=? order by buy_time DESC';
                $this->params = array($user_id, $status);
            }
            $this->doLimitResultList($page);
            $data = $this->result;
            $this->mmc->set('order_list_'.$user_id,$data,1,300);
        }
        return $data;
    }

    public function get_app_info($app_id){
        $data = $this->mmc->get("app_info".$app_id);
        if (!$data) {
            $this->sql = "select * from niuniu.apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function update_user_info($params,$user_id,$age){
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
        $user_info = array_merge($user_info,array("user_name"=>$params['user_name'],"id_number"=>$params['id_number'],"age"=>$age));
        $this->redis->hMset($user_id_md5,$user_info);
        $this->redis->lPush("user_login",$user_id_md5);
        $this->mmc->delete('user_info_'.$user_id);
        $this->mmc->delete('number_info_'.$user_id);
    }

    public function insert_operation_log($user_id,$op_type,$op_results,$op_desc){
        $guid = randomUtils::guid();
        $this->sql = "insert into user_operation_log(guid,user_id,op_type,op_results,op_desc,op_time) values (?,?,?,?,?,?)";
        $this->params = array($guid,$user_id,$op_type,$op_results,$op_desc,strtotime("now"));
        $this->doInsert();
    }

    public function get_user_info($id_number,$user_id){
        $data = $this->mmc->get("number_info_".$user_id);
        if(!$data){
            $this->sql = "select count(*) as num from user_info where id_number = ? and user_id != ?";
            $this->params = array($id_number,$user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("number_info_".$user_id,$data,1,300);
        }
        return $data;
    }

    public function set_session($key,$data){
        $this->mmc->set($key,$data,1,600);
    }

    public function get_session($key){
        $data = $this->mmc->get($key);
        return $data[$key];
    }

    public function del_session($key){
        $this->mmc->delete($key);
    }
}
