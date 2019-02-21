<?php
COMMON('niuniuDao');
class message_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admin_menus";
    }

    public function get_message($page,$params){
        $this->limit_sql = "select a.*,b.app_name from sdk_message_tb as a left join apps as b on a.appid = b.app_id where a.status <> 2 ";
        if($params['type']){
            $this->limit_sql = $this->limit_sql." and a.type = ".$params['type'];
        }
        if($params['sort_type']){
            $this->limit_sql = $this->limit_sql." and a.sort_type = ".$params['sort_type'];
        }
        if($params['channel']){
            $this->limit_sql = $this->limit_sql." and a.channel = '".$params['channel']."'";
        }
        if($params['appid']){
            $this->limit_sql = $this->limit_sql." and a.appid = ".$params['appid'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_game_list(){
        $data = $this->mmc->get("get_game_list".$_SESSION['usr_id']);
        if(!$data) {
            $this->sql = "select * from apps where status = 1 order by id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_game_list".$_SESSION['usr_id'],$data,1,300);
        }
        return $data;
    }

    public function get_guild_list(){
        $data = $this->mmc->get("get_guild_list".$_SESSION['usr_id']);
        if(!$data) {
            $this->sql = "select * from admins where is_del = 0 and group_id = 10 ";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_guild_list".$_SESSION['usr_id'],$data,1,300);
        }
        return $data;
    }

    public function insert_message($params){
        $this->sql = "insert into sdk_message_tb(title,`desc`,`type`,sort_type,user_group,appid,channel,add_time,subtitle) values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['title'],$params['desc'],$params['type'],$params['sort_type'],$params['user_group'],$params['app_id'],$params['channel'],time(),$params['subtitle']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_message_log($parent_id,$user_id){
        $this->sql = "select * from sdk_message_log where parent_id = ? and user_id = ?";
        $this->params = array($parent_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_message_log($parent_id,$user_id){
        $this->sql = "insert into sdk_message_log(parent_id,add_time,user_id) values (?,?,?)";
        $this->params = array($parent_id,time(),$user_id);
        $this->doInsert();
    }

    public function get_message_info($id){
        $this->sql = "select a.*,b.app_name from sdk_message_tb as a left join apps as b on a.appid = b.app_id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_message($params){
        $this->sql = "update sdk_message_tb set title=?,`desc`=?,`type`=?,sort_type=?,user_group=?,appid=?,channel=?,subtitle=? where id = ?";
        $this->params = array($params['title'],$params['desc'],$params['type'],$params['sort_type'],$params['user_group'],$params['app_id'],$params['channel'],$params['subtitle'],$params['id']);
        $this->doExecute();
    }

    public function update_message_info($params){
        $this->sql = "update sdk_message_tb set status = 1,push_time=? where id =?";
        $this->params = array($params['push_time'],$params['id']);
        $this->doExecute();
    }

    public function offline_message($id){
        $this->sql = "update sdk_message_tb set status = 2 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }
}