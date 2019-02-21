<?php
COMMON('dao','niuniuDao');
class news_dao extends niuniuDao {

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

    public function add_message_log($id,$user_id){
        $this->sql = "insert into sdk_message_log(parent_id, is_read, user_id, add_time)values(?,?,?,?)";
        $this->params = array($id, 1, $user_id, time());
        $this->doInsert();
    }

    public function update_message_log($id){
        $this->sql = "update sdk_message_log set is_read=? where id=?";
        $this->params = array(1,$id);
        $this->doExecute();
    }

    public function get_user_news_count($app_id,$user_id,$channel,$time){
//        $data = $this->mmc->get("news_count_".$user_id."_".$app_id."_".$channel."_".$time);
        if(!$data){
            $this->sql = "select count(1) as num from sdk_message_tb as tb left join sdk_message_log as l on tb.id=l.parent_id 
                      where tb.sort_type=1 and tb.appid=? and (tb.channel =? or tb.channel = '')  and l.user_id=? and tb.`status`=1 and tb.push_time > ? and tb.push_time < ? and l.is_read = 0";
            $this->params = array($app_id,$channel,$user_id,$time,time());
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("news_count_".$user_id."_".$app_id."_".$channel."_".$time, $data, 1,600);
        }
        return $data['num'];
    }

    public function get_game_news_count($app_id,$channel,$time){
        $this->sql = "select count(1) as num from sdk_message_tb where sort_type > 1 and appid=? and (channel=? or channel='') and push_time > ? and push_time < ? and `status`=1 ";
        $this->params = array($app_id,$channel,$time,time());
        $this->doResult();
        $data = $this->result;
        return $data['num'];
    }

    public function game_news_is_read_count($app_id,$user_id,$channel,$time){
        $this->sql = "select count(1) as num from sdk_message_tb as tb left join sdk_message_log as l on tb.id=l.parent_id 
                      where tb.sort_type >1 and tb.appid=? and (tb.channel =? or tb.channel = '')  and l.user_id=? and tb.`status`=1 and tb.push_time > ? and tb.push_time < ?";
        $this->params = array($app_id,$channel,$user_id,$time,time());
        $this->doResult();
        $data = $this->result;
        return $data['num'];
    }

    public function get_user_message_list($user_id,$app_id,$channel){
        $this->sql = "select tb.*,l.is_read from sdk_message_tb as tb left join sdk_message_log as l on tb.id=l.parent_id 
                      where tb.sort_type=? and tb.appid=? and (tb.channel =? or tb.channel = '')  and l.user_id=? and tb.`status`=? and tb.push_time < ? order by tb.push_time desc";
        $this->params = array(1,$app_id,$channel,$user_id,1,time());
        $this->doResultList();
        $data = $this->result;
        return $data;
    }

    public function get_app_message_list($app_id,$channel){
        $this->sql = "select * from sdk_message_tb where sort_type > 1 and appid=? and (channel=? or channel='') and push_time < ? and `status`=1  order by push_time desc ";
        $this->params = array($app_id,$channel,time());
        $this->doResultList();
        $data = $this->result;
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

    public function get_service($app_id,$user_id){
        $this->sql = "select count(*) as num from `66173`.sys_feedbacktb where user_id=? and appid=? and is_del=0 and read_status = 0";
        $this->params = array($user_id,$app_id);
        $this->doResult();
        return $this->result;
    }
}
