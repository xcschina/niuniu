<?php
COMMON('dao');
class deal_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "junwangka";
    }

    public function list_data($page){
        $this->limit_sql="select * from junwangka";
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_order($order_id, $product_id, $price, $amount, $par_price, $title, $account, $hj_order_id,$card_id,$card_password){
        $this->sql = "insert into junwangka(addtime, order_id, admin_id, product_id, price, par_price, product_title, account, amount , `status`, 
                    callback_time, hj_order_id,card_id, card_password)
                    values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(strtotime("now"), $order_id, $_SESSION['usr_id'], $product_id, $price, $par_price, $title, $account, $amount, 1,
                    time(), $hj_order_id,$card_id,$card_password);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}