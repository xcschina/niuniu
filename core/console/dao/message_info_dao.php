<?php
COMMON('dao');
class message_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function get_massages_list($params,$page){
        $this->limit_sql="select * from messages where 1=1";
        if($params['receiver_id'] && is_numeric($params['receiver_id'])){
            $this->limit_sql=$this->limit_sql." and receiver_id =".$params['receiver_id'];
        }
        if($params['title']){
            $this->limit_sql=$this->limit_sql." and title like '%".$params['title']."%'";
        }
        if($params['content']){
            $this->limit_sql=$this->limit_sql." and content like '%".$params['content']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_massages_info($params){
        $this->sql = "select * from messages where receiver_id=? and title=? and content=? and is_read=0";
        $this->params=array($params['receiver_id'],$params['title'],$params['content']);
        $this->doResult();
        return $this->result;
    }

    public function add_massages_info($params){
        $this->sql="insert into messages(receiver_id,title,content,add_time)values(?,?,?,?)";
        $this->params=array($params['receiver_id'],$params['title'],$params['content'],strtotime("now"));
        $this->doInsert();
    }
}