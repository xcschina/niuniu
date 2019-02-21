<?php
COMMON('dao');
class hengjingwendao_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "hengjingwendao";
    }

    public function list_data($page,$params){
        $this->limit_sql="select * from hengjingwendao WHERE 1=1";
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
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_order($order_id, $product_id, $price, $amount, $par_price, $title, $qq, $hj_order_id,$status_info,$game_id){
        $this->sql = "insert into hengjingwendao(addtime, order_id, admin_id, product_id, price, par_price, product_title, qq, amount, hj_order_id,status_info,game_id)
                    values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(strtotime("now"), $order_id, $_SESSION['usr_id'], $product_id, $price, $par_price, $title, $qq, $amount, $hj_order_id,$status_info,$game_id);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_all_list($params){
        $this->sql="select * from hengjingwendao WHERE 1=1";
        if($params['order_id']){
            $this->sql = $this->sql." and order_id = ".$params['order_id'];
        }
        if($params['product_id']){
            $this->sql = $this->sql." and product_id = ".$params['product_id'];
        }
        if($params['qq']){
            $this->sql = $this->sql." and qq = ".$params['qq'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->sql = $this->sql." and `status` = ".$params['status'];
        }
        $this->sql=$this->sql." order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_operation_name(){
        $this->sql = "SELECT a.id,a.real_name FROM hengjingwendao h INNER JOIN niuniu.admins a ON
                  h.admin_id=a.id GROUP BY h.admin_id";
        $this->doResultList();
        return $this->result;
    }
}