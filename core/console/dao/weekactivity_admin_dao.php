<?php
COMMON('dao');
class weekactivity_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_weekactivity_list($page){
        $this->limit_sql = "select * from weekactivity ORDER by status DESC ";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_weekactivity_nouse(){
        $this->sql = "select * from weekactivity where batch_id is null ORDER by batch_id ASC";
        $this->doResultList();
        return $this->result;
    }

    public function get_weekactivity_status(){
        $this->sql ="select * from weekactivity where status = 1";
        $this->doResultList();
        return $this->result;
    }

    public function get_game_list($id){
        $this->sql = "select id,game_name,game_icon from game where status=1 and is_del= 0";
        if(!empty($id)){
            $this->sql = $this->sql .=" and id = ?";
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_game_icon($id){
        $this->sql = "select game_icon from game";
        if(!empty($id)){
            $this->sql = $this->sql .=" where id = ?";
        }
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_activity_tb($params){
        $this->sql = "insert into weekactivity(game_id,game_img,game_name,old_price,new_price,repertory,false_reper,game_desc)
                      VALUES(?,?,?,?,?,?,?,?)";
        $this->params=array($params['game_id'],$params['game_img'],$params['game_name'],$params['old_price'],$params['new_price'],$params['repertory'],$params['false_reper'],$params['game_desc']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function insert_activity_batch($params){
        $this->sql = "insert into weekactivity_batch(pc_img,m_img,batch_name,start_time,end_time,game_rule)
                      VALUES(?,?,?,?,?,?)";
        $this->params=array($params['pc_img'],$params['m_img'],$params['batch_name'],$params['start_time'],$params['end_time'],$params['game_rule']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_activity_list($id){
        $this->sql = "select * from weekactivity where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function activity_batch_list($id){
        $this->sql = "select * from weekactivity_batch";
        if(!empty($id)){
            $this->sql = $this->sql .=" where id=".$id;
        }
        $this->params=array();
        $this->doResultList();
        return $this->result;
    }

    public function activity_batch_all($page){
        $this->limit_sql = "select * from weekactivity_batch ORDER BY status DESC";
        $this->params=array();
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_weekactivity_batch($params){
        $this->sql="update weekactivity_batch set pc_img=?,m_img=?,start_time=?,end_time=?,batch_name=?,game_rule=?";
        $this->sql = $this->sql.=" where id =?";
        $this->params=array($params['pc_img'],$params['m_img'],$params['start_time'],$params['end_time'],$params['batch_name'],$params['game_rule'],$params['id']);
        $this->doExecute();
    }

    public function update_weekactivity_tb($params){
        $this->sql="update weekactivity set game_id=?,game_name=?,old_price=?,new_price=?,repertory=?,game_desc=?,false_reper=?,game_img=?";
        $this->sql = $this->sql.=" where id =?";
        $this->params=array($params['game_id'],$params['game_name'],$params['old_price'],$params['new_price'],$params['repertory'],$params['game_desc'],$params['false_reper'],$params['game_img'],$params['id']);
        $this->doExecute();
    }

    public function update_weekactivity_status($status,$id){
        $this->sql="update weekactivity set status = ? where id = ? ";
        $this->params=array($status,$id);
        $this->doExecute();
    }

    public function update_activity_message($batch_id,$id){
        $this->sql = "update weekactivity set batch_id = ? where id = ? ";
        $this->params=array($batch_id,$id);
        $this->doExecute();
    }

    public function update_activity_batch($id){
        $this->sql = "update weekactivity_batch set is_use = 1 where id = ? ";
        $this->params=array($id);
        $this->doExecute();
    }

    public function update_batch_status($status,$batch_id){
        $this->sql = "update weekactivity_batch set status =? where id =? ";
        $this->params=array($status,$batch_id);
        $this->doExecute();
    }

    public function get_weekactivity_batch($id){
        $this->sql = "select * from weekactivity_batch where id = ? ";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }


}