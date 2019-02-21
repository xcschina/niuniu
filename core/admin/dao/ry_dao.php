<?php
COMMON('niuniuDao');
class ry_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_ry_list($page,$params){
        $this->limit_sql=" select * from ry_info where 1=1 ";
        if($params['is_del']=='1'){
            $this->limit_sql=$this->limit_sql." and is_del = '1'";
        }else{
            $this->limit_sql=$this->limit_sql." and is_del = '0'";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_all_app(){
        $this->sql = "select * from apps order by id desc";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_ry_info($id){
        $this->sql = "select * from ry_info where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_ry_info($params){
        $this->sql = "insert into ry_info(app_id, ry_appid, channel, game_name, add_time)values(?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['ry_appid'],$params['channel'],$params['game_name'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_ry_info($params,$id){
        $this->sql = "update ry_info set app_id=?,ry_appid=?,channel=?,game_name=?,is_del=? where id=?";
        $this->params = array($params['app_id'],$params['ry_appid'],$params['channel'],$params['game_name'],$params['is_del'],$id);
        $this->doExecute();
    }

    public function get_ry_ext_list($page,$params){
        $this->limit_sql="select * from ry_extend_tb where 1=1";
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_ext_ry_info($params){
        $this->sql = "insert into ry_extend_tb(channel_name, activity_name, url, short_url,apple_id,act_id,add_time)values(?,?,?,?,?,?,?)";
        $this->params = array($params['channel_name'],$params['activity_name'],$params['url'],$params['short_url'],$params['apple_id'],$params['act_id'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_ry_ext_info($id){
        $this->sql = "select * from ry_extend_tb where id =? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_ry_ext_act_id($act_id,$id=''){
        $this->sql = "select * from ry_extend_tb where act_id = '".$act_id."'";
        if(!empty($id)){
            $this->sql = $this->sql." and id !=".$id;
        }
        $this->doResult();
        return $this->result;
    }

    public function update_ry_ext_info($params,$id){
        $this->sql = "update ry_extend_tb set channel_name=?,activity_name=?,apple_id=?,act_id=?,add_time=? where id=?";
        $this->params = array($params['channel_name'],$params['activity_name'],$params['apple_id'],$params['act_id'],time(),$id);
        $this->doExecute();
    }
}
