<?php
COMMON('dao');
class kamen_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "kamen";
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function list_data($page,$params){
        $this->limit_sql = "select * from kamen where 1=1";
        if($params['order_id']){
            $this->limit_sql = $this->limit_sql." and order_id = ".$params['order_id'];
        }
        if($params['product_id']){
            $this->limit_sql = $this->limit_sql." and product_id = ".$params['product_id'];
        }
        if($params['qq']){
            $this->limit_sql = $this->limit_sql." and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql = $this->limit_sql." and `status` = ".$params['status'];
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_order($order_id, $product_id, $amount, $purchase_price, $qq, $kamen_order_id){
        $this->sql = "insert into kamen(order_id, product_id, amount, purchase_price, qq, kamen_order_id, add_time, admin_id)values(?,?,?,?,?,?,?,?)";
        $this->params = array($order_id, $product_id, $amount, $purchase_price, $qq, $kamen_order_id, strtotime("now"), $_SESSION['usr_id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}