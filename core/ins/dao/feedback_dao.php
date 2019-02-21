<?php
COMMON('dao');
class feedback_dao extends Dao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_fag($app_id){
        $data = memcache_get($this->mmc, "feedback_faq_".$app_id);
        if(!$data){
            $this->sql = "select * from sys_faq where appid=? and module_id=7 order by id desc";
            $this->params = array($app_id);
            $this->doResultList();
            $data =  $this->result;
            memcache_set($this->mmc, "feedback_faq_".$app_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_my_problem($app_id, $usr_id, $page = 0, $num=10) {
//        $data = memcache_get($this->mmc, 'sdk_feedback_info_'.$usr_id.'_'.$app_id);
//        if(!$data) {
//            $this->sql = 'select * from sys_feedbacktb
//                where user_id=? and appid=? and is_del=0 order by create_time desc';
//            $this->params = array($usr_id, $app_id);
//            $this->doResultList();
//            $data = $this->result;
//            memcache_set($this->mmc, 'sdk_feedback_info_'.$usr_id.'_'.$app_id, $data, 1, 600);
//        }
//        if($data) {
//            $data = array_chunk($data, $num);
//            if($data[$page]) {
//                return $data[$page];
//            }
//        }
//        return array();
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

    //这个需要改为用es查询
    public function get_my_games($user_id, $appid){
        $this->sql = "select servname,nickname,playerid from sys_common_game_user_tb where pid=? and userid=? and servname!='' group by servid order by add_time";
        $this->params = array($appid, $user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_notice_by_appid($appid){
        $this->sql = 'select * from sys_feedback_notice where appid = ? ';
        $this->params = array($appid);
        $this->doResult();
        $data = $this->result;
        return $data['notice'];
    }

    public function insert_account_back($data){
        $this->sql = 'insert into sys_account_back(uuid, mobile, qq , transfer_id, user_info, device,
            pay_order, img_path, creation_time, last_time,`other`,add_time) values(?,?,?,?,?,?,?,?,?,?,?,?)';
        $this->params = array($data['uuid'], $data['mobile'], $data['qq'], $data['transfer_id'], $data['user_info'], $data['device'],
            $data['pay_order'], $data['img_path'], $data['creation_time'], $data['last_time'], $data['other'] ,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_question_status($feedback_id, $user_id, $app_id){
        $this->sql = 'update sys_feedbacktb set read_status=1 where id=?';
        $this->params = array($feedback_id);
        $this->doExecute();
        memcache_delete($this->mmc, "sdk_feedback_info_".$user_id.'_'.$app_id);
    }

    public function get_feedback_info($id) {
        $this->sql = 'select * from sys_feedbacktb where id=? and is_del=0';
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
            memcache_set($this->mmc, 'help_app_name_' . $app_id, $app_name, 0, 86400);
        }
        return $app_name;
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
        $this->sql = "select * from sys_feedbacktb
                where user_id=? and appid=? and is_del=0 order by create_time desc limit 3";
        $this->params = array($user_id,$appid);
        $this->doResultList();
        return $this->result;
    }

    public function update_feedback_reply($id){
        $this->sql = 'update sys_feedbacktb set question_status = 1 where id =?';
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_common_question(){
        $this->sql = "select * from `niuniu`.common_question where is_show = 0 and is_del = 0 order by id desc limit 4";
        $this->doResultList();
        return $this->result;
    }

    public function get_reply_list($id){
        $this->sql = "select a.*,b.nick_name from `niuniu`.reply_feedback a left join user_info b on a.user_id = b.user_id  where pid = ?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_reply_feedback($params){
        $this->sql = 'insert into `niuniu`.reply_feedback (pid,user_id,`desc`,add_time) values (?,?,?,?)';
        $this->params = array($params['pid'],$params['usr_id'],$params['desc'],time());
        $this->doInsert();

        $this->sql = 'update sys_feedbacktb set is_reply = 0 where id =?';
        $this->params = array($params['pid']);
        $this->doExecute();
    }

    public function get_question_list(){
        $data = memcache_get($this->mmc, 'sdk_question_list');
        if(!$data) {
            $this->sql = 'select * from `niuniu`.common_question where is_del = 0 order by add_time desc';
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'sdk_question_list', $data, 1, 600);
        }
        return $data;
    }

    public function get_question_info($id){
        $this->sql = "select * from `niuniu`.common_question where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_question($user_id,$app_id){
        $this->sql = "select * from sys_feedbacktb where user_id = ? and appid = ? and read_status = 0 and is_del = 0";
        $this->params = array($user_id,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_question($params){
        $this->sql = "select * from `niuniu`.reply_feedback where pid = ? order by id desc";
        $this->params = array($params['pid']);
        $this->doResultList();
        return $this->result;
    }

    public function get_uuid_info($uuid){
        $this->sql = "select * from sys_account_back where uuid= ? order by add_time desc";
        $this->params = array($uuid);
        $this->doResult();
        return $this->result;
    }

    public function set_usr_session($key, $data){
        $session_data = memcache_get($this->mmc, "feedback-session-".session_id());
        $session_data[$key] = $data;
        memcache_set($this->mmc, "feedback-session-".session_id(), $session_data, 1, 600);
    }

    public function get_usr_session($key){
        $session_data = memcache_get($this->mmc, "feedback-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }
}