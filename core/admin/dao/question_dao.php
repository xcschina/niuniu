<?php
COMMON('niuniuDao');
class question_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_question_list($page,$params){
        $this->limit_sql = "select * from common_question where is_del = 0";
        if($params['is_show'] && is_numeric($params['is_show']) || $params['is_show'] === '0'){
            $this->limit_sql .= " and is_show = ".$params['is_show'];
        }
        $this->limit_sql .= " order by add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_show_num($id){
        $this->sql = "select count(*) as num from common_question where is_show = 0 and is_del = 0 and id != ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_show(){
        $this->sql = "select count(*) as num from common_question where is_show = 0 and is_del = 0 ";
        $this->doResult();
        return $this->result;
    }

    public function insert_question($params){
        $this->sql = "insert into common_question (title,content,is_show,add_time) values (?,?,?,?)";
        $this->params = array($params['title'],$params['content'],$params['is_show'],time());
        $this->doInsert();
        $this->mmc->delete('sdk_question_list');
    }

    public function get_question_info($id){
        $this->sql = "select * from common_question where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_question($params){
        $this->sql = "update common_question set title = ?,content = ?,is_show = ? where id = ?";
        $this->params = array($params['title'],$params['content'],$params['is_show'],$params['id']);
        $this->doExecute();
        $this->mmc->delete('sdk_question_list');
    }

    public function delete_question($id){
        $this->sql = "update common_question set is_del = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
        $this->mmc->delete('sdk_question_list');
    }
}