<?php
COMMON('dao');
class guanming_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "guanming";
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function list_data($page,$params){
        $this->limit_sql = "select * from guanming where 1=1";
        if($params['order_id']){
            $this->limit_sql = $this->limit_sql." and order_id = ".$params['order_id'];
        }
        if($params['title']){
            $this->limit_sql = $this->limit_sql." and title = ".$params['title'];
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
    public function insert_order($username,$order_id, $price, $number,$qq , $title, $gm_order_id,$status,$ret){
        $this->sql = "insert into guanming(username,order_id, price, amount, qq, `title`, gm_order_id, add_time, admin_id,status,ret)values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($username,$order_id, $price, $number, $qq, $title, $gm_order_id, strtotime("now"), $_SESSION['usr_id'],$status,$ret);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}