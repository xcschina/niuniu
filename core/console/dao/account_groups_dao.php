<?php
COMMON('dao');
class account_groups_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function get_groups_list(){
        $this->sql = "select * from admin_groups order by ID ";
        $this->doResultList();
        return $this->result;
    }

    public function get_info($id){
        $this->sql = "select * from admin_groups where ID=".$id;
        $this->doResult();
        return $this->result;
    }

    public function insert_data($group){
        $this->sql = "insert into admin_groups(GroupName,Status) values(?,?)";
        $this->params = array($group['groups_name'] , $group['groups_status']);
        $this->doExecute();
    }

    public function update_data($group){
        $this->sql = "update admin_groups set GroupName=?, Status=? where ID=?";
        $this->params = array($group['groups_name'] , $group['groups_status'],$group['id']);
        $this->doExecute();

    }

}