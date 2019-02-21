<?php
COMMON('dao');
class bespeak_dao extends Dao
{
    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game(){
        $this->sql = "select game_name,id from game where is_del = 0 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_bespeak($params,$page){
        $this->limit_sql = "select a.*,b.game_name from bespeak_tb as a left join game as b on a.game_id = b.id where a.is_del = 0 ";
        if($params['game_id']){
            $this->limit_sql = $this->limit_sql." and a.game_id = " .$params['game_id'];
        }
        if($params['bespeak_id']){
            $this->limit_sql = $this->limit_sql. " and a.id = ".$params['bespeak_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_bespeak_name(){
        $this->sql = "select `name`,id from bespeak_tb where is_del = 0";
        $this->doResultList();
        return $this->result;
    }

    public function get_bespeak_prize($bespeak_id){
        $this->sql = "select * from prize_channel where bespeak_id = ?";
        $this->params = array($bespeak_id);
        $this->doResult();
        return $this->result;
    }

    public function add_bespeak($params){
        $this->sql = "insert into bespeak_tb(`name`,game_id,virtual_num,down_url,activity_rules,start_time,end_time,share_title,share_msg,share_img,share_desc) values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['name'],$params['game_id'],$params['virtual_num'],$params['down_url'],$params['activity_rules'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_img'],$params['share_desc']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_bespeak($params){
        $this->sql = "update bespeak_tb set `name`=?,game_id=?,virtual_num=?,down_url=?,activity_rules=?,start_time=?,end_time=?,share_title=?,share_msg=?,share_img=?,share_desc=? where id = ?";
        $this->params = array($params['name'],$params['game_id'],$params['virtual_num'],$params['down_url'],$params['activity_rules'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_img'],$params['share_desc'],$params['id']);
        $this->doExecute();
    }

    public function prize_add($id,$prize_channel){
        foreach ($prize_channel as $key=>$code) {
            $this->sql = "insert into prize_channel(bespeak_id,position_id) values(?,?)";
            $this->params = array($id,$key);
            $this->doInsert();
        }
    }

    public function get_bespeak_info($id){
        $this->sql = "select * from bespeak_tb where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_prize($id){
        $this->sql = "select * from prize_channel where bespeak_id = ?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function update_prize_channel($bespeak_id, $params,$position_id)
    {
        $this->sql = "update prize_channel set prize_name=?,prize_chance=?,prize_type=?,relation_id=? where bespeak_id=? and position_id=?";
        $this->params = array($params['prize_name'],$params['prize_chance'],$params['prize_type'],$params['relation_id'],$bespeak_id,$position_id);
        $this->doExecute();
    }

    public function prize_channel_add($bespeak_id, $params, $position_id){
        $this->sql = "insert into prize_channel(bespeak_id,position_id,prize_name,prize_chance,prize_type,relation_id) values(?,?,?,?,?,?) ";
        $this->params = array($bespeak_id,$position_id,$params['prize_name'],$params['prize_chance'],$params['prize_type'],$params['relation_id']);
        $this->doInsert();
    }

    public function del_bespeak($id){
        $this->sql = "update bespeak_tb set is_del = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

}