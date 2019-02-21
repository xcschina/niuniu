<?php
COMMON('dao');
class now_pay_id_check_dao extends Dao{

    public function __construct(){
        parent::__construct();
    }

    public function get_user_by_id_card($id_card){
        $this->sql = "SELECT * FROM user_id_card_check WHERE id_card=? AND statu=1";
        $this->params = array($id_card);
        $this->doResult();
        return $this->result;
    }

    public function insert_user_id_card($id_card,$real_name,$statu){
        $this->sql = "INSERT INTO user_id_card_check(id_card,real_name,add_time,statu)VALUES(?,?,?,?)";
        $this->params = array($id_card,$real_name,time(),$statu);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}