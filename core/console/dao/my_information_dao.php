<?php
COMMON('dao');
class my_information_dao extends Dao{
    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "admins";
    }

    public function  get_user_info(){
        $this->sql="select * from admins WHERE id='".$_SESSION['usr_id']."'";
        $this->doResult();
        return $this->result;
    }
}