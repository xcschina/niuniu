<?php
COMMON('dao');
class system_password_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "admins";
    }

    public function checkPwd($userID){
        $this->sql = "select * from admins where is_del=0 and  id='".$userID."'";
        $this->doResult();
        return $this->result;
    }

    public function updateAdminPwd($adminPwd,$id){
        $this->sql = "update admins set usr_pwd='".$adminPwd."' where id='".$id."'";
        $this->doExecute();
    }

}