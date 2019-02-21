<?php
COMMON('dao','niuniuDao');
class web_dao extends niuniuDao {

	public function __construct() {
		parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
	}

    public function set_usr_session($key, $data){
        $session_data = $this->mmc->get("feedback-session-".session_id());
        $session_data[$key] = $data;
        $this->mmc->set("feedback-session-".session_id(), $session_data, 1,600);
    }

    public function get_usr_session($key){
        $session_data = $this->mmc->get("feedback-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }

    public function get_message_details($id,$appid){
//        $data = $this->mmc->get("message_details_".$id."_".$appid);
        if(!$data){
            $this->sql = "select * from sdk_message_tb where id=? and appid=? ";
            $this->params = array($id, $appid);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("message_details_".$id."_".$appid, $data, 1,600);
        }
        return $data;
    }

    public function get_message_log($id,$user_id){
        $this->sql = "select * from sdk_message_log where parent_id=? and user_id=? ";
        $this->params = array($id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function add_message_log($id,$user_id,$app_id){
        $this->sql = "insert into sdk_message_log(parent_id, is_read, user_id, add_time)values(?,?,?,?)";
        $this->params = array($id, 1, $user_id, time());
        $this->doInsert();
        $this->mmc->delete('get_user_message_list_'.$user_id.'_'.$app_id);
        $this->mmc->delete('get_app_message_list_'.$app_id);
        $this->mmc->delete('get_user_message_'.$user_id.'_'.$app_id);
        $this->mmc->delete('is_read_'.$user_id.'_'.$app_id);
    }

    public function update_message_log($id,$user_id,$app_id){
        $this->sql = "update sdk_message_log set is_read=? where id=?";
        $this->params = array(1,$id);
        $this->doExecute();
        $this->mmc->delete('get_user_message_list_'.$user_id.'_'.$app_id);
        $this->mmc->delete('get_app_message_list_'.$app_id);
        $this->mmc->delete('get_user_message_'.$user_id.'_'.$app_id);
        $this->mmc->delete('is_read_'.$user_id.'_'.$app_id);
    }

    public function get_uuid_info($uuid){
        $this->sql = "select * from `66173`.sys_account_back where uuid= ? order by add_time desc";
        $this->params = array($uuid);
        $this->doResult();
        return $this->result;
    }

    public function get_user_message_list($user_id,$app_id,$channel){
//        $data = $this->mmc->get('get_user_message_list_'.$user_id."_".$app_id);
        if(!$data){
            $this->sql = "select tb.*,l.is_read from sdk_message_tb as tb left join sdk_message_log as l on tb.id=l.parent_id 
                      where tb.sort_type=? and tb.appid=? and (tb.channel =? or tb.channel = '')  and l.user_id=? and tb.`status`=? and tb.push_time < ? order by tb.push_time desc";
            $this->params = array(1,$app_id,$channel,$user_id,1,time());
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('get_user_message_list_'.$user_id."_".$app_id,$data,1,600);
        }
        return $data;
    }

    public function get_user_message($user_id,$app_id,$channel){
//        $data = $this->mmc->get('get_user_message_'.$user_id.'_'.$app_id);
        if(!$data){
            $this->sql = "select l.* from sdk_message_tb as tb left join sdk_message_log as l on tb.id = l.parent_id where tb.sort_type = ? and tb.appid=? and (tb.channel =? or tb.channel = '')  and l.user_id=? and tb.`status` = ? and tb.push_time < ? and l.is_read = 0";
            $this->params = array(1,$app_id,$channel,$user_id,1,time());
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('get_user_message_'.$user_id.'_'.$app_id,$data,1,600);
        }
        return $data;
    }

    public function get_app_message_list($app_id,$channel){
//        $data = $this->mmc->get("get_app_message_list_".$app_id);
        if(!$data){
            $this->sql = "select * from sdk_message_tb where sort_type > 1 and appid=? and (channel=? or channel='') and push_time < ? and `status`=1  order by push_time desc ";
            $this->params = array($app_id,$channel,time());
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_app_message_list_".$app_id,$data,1,600);
        }
        return $data;
    }

    public function get_user_msg_log($parent_id,$user_id){
        $this->sql = "select * from sdk_message_log where parent_id=? and user_id=?";
        $this->params = array($parent_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_mmc_data($name){
       $data = $this->mmc->get($name);
       if(!$data){
           return "";
       }
       return $data;
    }

    public function set_mmc_data($name,$data){
        $this->mmc->set($name, $data, 1,600);
    }

    public function update_question_status($feedback_id, $user_id, $app_id){
        $this->sql = 'update `66173`.sys_feedbacktb set read_status=1 where id=?';
        $this->params = array($feedback_id);
        $this->doExecute();
        memcache_delete($this->mmc, "sdk_feedback_info_".$user_id.'_'.$app_id);
    }

    public function get_feedback_info($id) {
        $this->sql = 'select * from `66173`.sys_feedbacktb where id=? and is_del=0';
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_app_name($app_id){
        $app_name = memcache_get($this->mmc, 'help_app_name_' . $app_id);
        if (!$app_name) {
            COMMON('niuniuDao');
            $niuniu = new niuniuDao();
            $niuniu->sql = 'select app_name from apps where app_id=?';
            $niuniu->params = array($app_id);
            $niuniu->doResult();
            $app_name = $niuniu->result['app_name'];
            memcache_set($this->mmc, 'help_app_name_' . $app_id, $app_name, 1, 86400);
        }
        return $app_name;
    }

    public function get_fag($app_id){
        $data = memcache_get($this->mmc, "feedback_faq_".$app_id);
        if(!$data){
            $this->sql = "select * from `66173`.sys_faq where appid=? and module_id=7 order by id desc";
            $this->params = array($app_id);
            $this->doResultList();
            $data =  $this->result;
            memcache_set($this->mmc, "feedback_faq_".$app_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_my_problem($app_id, $usr_id) {
        $data = memcache_get($this->mmc, 'sdk_feedback_info_'.$usr_id.'_'.$app_id);
        if(!$data) {
            $this->sql = 'select * from `66173`.sys_feedbacktb
                where user_id=? and appid=? and is_del=0 order by create_time desc';
            $this->params = array($usr_id, $app_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'sdk_feedback_info_'.$usr_id.'_'.$app_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_notice_by_appid($appid){
        $this->sql = 'select * from `66173`.sys_feedback_notice where appid = ? ';
        $this->params = array($appid);
        $this->doResult();
        $data = $this->result;
        return $data['notice'];
    }

    public function insert_account_back($data){
        $this->sql = 'insert into `66173`.sys_account_back(uuid, mobile, qq , transfer_id, user_info, device,
            pay_order, img_path, creation_time, last_time,`other`,add_time) values(?,?,?,?,?,?,?,?,?,?,?,?)';
        $this->params = array($data['uuid'], $data['mobile'], $data['qq'], $data['transfer_id'], $data['user_info'], $data['device'],
            $data['pay_order'], $data['img_path'], $data['creation_time'], $data['last_time'], $data['other'] ,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_feedback($data){
        $this->sql = 'insert into `66173`.sys_feedbacktb(appid, user_id, gamever, net, mtype,
            osname, osver, logintype, sdkver,`server_name`,img_path,
            role_name, player_id, content, create_time, `type`,mobile,read_status) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $this->params = array($data['appid'], $data['usr_id'], $data['gamever'], $data['net'], $data['mtype'],
            $data['systemnmae'], $data['osver'], $data['logintype'], $data['sdkver'], $data['server_name'],  $data['img_path'],
            $data['nick_name'], $data['player_id'], $data['content'], date('Y-m-d H:i:s'),$data['type'],$data['mobile'],1);
        $this->doInsert();
        memcache_delete($this->mmc, "feedback_list_".$data['appid'].'_'.$data['usr_id']);
        memcache_delete($this->mmc, 'sdk_feedback_info_'.$data['usr_id'].'_'.$data['appid']);
        return $this->LAST_INSERT_ID;
    }

    public function get_service($appid,$user_id){
        $this->sql = "select * from `66173`.sys_feedbacktb
                where user_id=? and appid=? and is_del=0 order by create_time desc limit 3";
        $this->params = array($user_id,$appid);
        $this->doResultList();
        return $this->result;
    }

    public function update_feedback_reply($id){
        $this->sql = 'update `66173`.sys_feedbacktb set question_status = 1 where id =?';
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_common_question(){
        $this->sql = "select * from common_question where is_show = 0 and is_del = 0 order by id desc limit 4";
        $this->doResultList();
        return $this->result;
    }

    public function get_reply_list($id){
        $this->sql = "select a.*,b.nick_name from reply_feedback a left join `66173`.user_info b on a.user_id = b.user_id  where pid = ?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_reply_feedback($params){
        $this->sql = 'insert into reply_feedback (pid,user_id,`desc`,add_time) values (?,?,?,?)';
        $this->params = array($params['pid'],$params['user_id'],$params['desc'],time());
        $this->doInsert();

        $this->sql = 'update `66173`.sys_feedbacktb set is_reply = 0 where id =?';
        $this->params = array($params['pid']);
        $this->doExecute();
    }

    public function get_question_list(){
        $data = memcache_get($this->mmc, 'sdk_question_list');
        if(!$data) {
            $this->sql = 'select * from common_question where is_del = 0 order by add_time desc';
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'sdk_question_list', $data, 1, 600);
        }
        return $data;
    }

    public function get_question_info($id){
        $this->sql = "select * from common_question where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_question($user_id,$app_id){
        $this->sql = "select * from `66173`.sys_feedbacktb where user_id = ? and appid = ? and read_status = 0 and is_del = 0";
        $this->params = array($user_id,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_question($params){
        $this->sql = "select * from reply_feedback where pid = ? order by id desc";
        $this->params = array($params['pid']);
        $this->doResultList();
        return $this->result;
    }
}
