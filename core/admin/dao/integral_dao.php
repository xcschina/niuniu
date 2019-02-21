<?php
COMMON('niuniuDao');
class integral_dao extends niuniuDao {
    public function __construct() {
        parent::__construct();
    }

    public function get_integral_list($page){
        $this->limit_sql = "select * from nnb_integral where is_del = 0";
        $this->limit_sql .= " order by add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_money_info($money){
        $this->sql = "select * from nnb_integral where money = ? and is_del = 0";
        $this->params = array($money);
        $this->doResult();
        return $this->result;
    }

    public function insert_integral($params){
        $this->sql = "insert into nnb_integral(money,integral,add_time) values(?,?,?)";
        $this->params = array($params['money'],$params['integral'],time());
        $this->doExecute();
    }

    public function get_integral_info($id){
        $this->sql = "select * from nnb_integral where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_info($params){
        $this->sql = "select * from nnb_integral where money = ? and id !=? and is_del = 0";
        $this->params = array($params['money'],$params['id']);
        $this->doResult();
        return $this->result;
    }

    public function update_integral($params){
        $this->sql = "update nnb_integral set money = ?,integral = ? where id =?";
        $this->params = array($params['money'],$params['integral'],$params['id']);
        $this->doExecute();
    }

    public function delete_integral($id){
        $this->sql = "update nnb_integral set is_del = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }
}