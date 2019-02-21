<?php
COMMON('dao');
class activity_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function activity_name(){
        $this->sql = "select title,id from 66app_activity_tb where is_del=0";
        $this->doResultList();
        return $this->result;
    }

    public function activity_list($params,$page){
        $this->limit_sql = "select * from 66app_activity_tb where is_del=0";
        if($params['title']){
            $this->limit_sql = $this->limit_sql." and title like '%".$params['title']."%'";
        }
        if($params['start_time']){
            $this->limit_sql = $this->limit_sql." and start_time = ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql = $this->limit_sql." and end_time = ".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_activity_info($params){
        $this->sql = "select * from 66app_activity_tb where is_del=0 and title=?";
        $this->params = array($params['title']);
        $this->doResult();
        return $this->result;
    }

    public function add_activity($params){
        $this->sql = "insert into 66app_activity_tb(title,content,start_time,end_time,banner,url,`type`,is_del,add_time) values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['title'],$params['content'],strtotime($params['start_time']),strtotime($params['end_time']),$params['banner'],$params['url'],$params['type'],'0',time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_activity($id){
        $this->sql = "select * from 66app_activity_tb where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function edit_activity($params){
        $this->sql = "update 66app_activity_tb set title=?,banner=?,content=?,start_time=?,end_time=?,url=?,`type`=?,add_time=? where id=?";
        $this->params = array($params['title'],$params['banner'],$params['content'],strtotime($params['start_time']),strtotime($params['end_time']),$params['url'],$params['type'],time(),$params['id']);
        $this->doExecute();
    }

    public function del_activity($id){
        $this->sql = "update 66app_activity_tb set is_del=1 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_pop_list($params,$page){
        $this->limit_sql = "select a.*,g.game_name,t.m_title from 66app_activity_pop as a left join 66app_game_tb as g on a.game_id=g.id left join 66app_disc_theme_tb as t on a.theme_id=t.id where a.is_del=0 ";
        if($params['game_id']){
            $this->limit_sql .= " and a.game_id =".$params['game_id'];
        }
        if($params['theme_id']){
            $this->limit_sql .= " and a.theme_id = ".$params['theme_id'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and start_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and end_time <=".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_name_list(){
        $this->sql = "select * from 66app_game_tb where is_del = 0";
        $this->doResultList();
        return $this->result;
    }

    public function get_theme_list(){
        $this->sql = "select * from 66app_disc_theme_tb where is_del = 0";
        $this->doResultList();
        return $this->result;
    }

    public function get_rec_list(){
        $this->sql = "select * from 66app_game_tb where is_del = 0 and is_disc_rec = 1";
        $this->doResultList();
        return $this->result;
    }

    public function insert_activity_pop($params){
        $this->sql = "insert into 66app_activity_pop(title,img,status,start_time,end_time,add_time,url,game_id,theme_id,`type`) values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['title'],$params['img'],$params['status'],strtotime($params['start_time']),strtotime($params['end_time']),time(),$params['url'],$params['game_id'],$params['theme_id'],$params['type']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_pop_info($id){
        $this->sql = "select * from 66app_activity_pop where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_activity_pop($params){
        $this->sql = "update 66app_activity_pop set title = ?,img = ?,status = ?,start_time = ?,end_time = ?,url = ?,game_id = ?,theme_id = ?,`type` = ? where id =?";
        $this->params = array($params['title'],$params['img'],$params['status'],strtotime($params['start_time']),strtotime($params['end_time']),$params['url'],$params['game_id'],$params['theme_id'],$params['type'],$params['id']);
        $this->doExecute();
    }

    public function del_activity_pop($id){
        $this->sql = "update 66app_activity_pop set is_del = 1 where id= ?";
        $this->params = array($id);
        $this->doExecute();
}
}