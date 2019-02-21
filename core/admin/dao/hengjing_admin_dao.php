<?php
COMMON('dao');
class hengjing_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->TB_NAME = "hengjing";
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function list_data($page,$params){
//        $this->limit_sql="select * from hengjing";
//        $this->limit_sql=$this->limit_sql." order by id desc";
//        $this->doLimitResultList($page);
//        return $this->result;
        $this->limit_sql="select * from hengjing WHERE 1=1";
        if($params['order_id']){
            $this->limit_sql = $this->limit_sql." and order_id = ".$params['order_id'];
        }
        if($params['product_id_search']){
            $this->limit_sql = $this->limit_sql." and product_id = ".$params['product_id_search'];
        }
        if($params['qq_search']){
            $this->limit_sql = $this->limit_sql." and qq = ".$params['qq_search'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql = $this->limit_sql." and `status` = ".$params['status'];
        }
        if($params['admin_id']){
            $this->limit_sql = $this->limit_sql." and `admin_id` = ".$params['admin_id'];
        }
        if ($params['game_id_search'] && is_numeric($params['game_id_search']) || $params['game_id_search'] === "0"){
            $this->limit_sql = $this->limit_sql." and `game_id` = ".$params['game_id_search'];
        }
        if($params['start_time']){
            $this->limit_sql = $this->limit_sql." and  addtime>=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql = $this->limit_sql." and  addtime<".strtotime($params['end_time']);
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

/*    public function insert_order($order_id, $product_id, $price, $amount, $par_price, $title, $qq, $hj_order_id){
        $this->sql = "insert into hengjing(addtime, order_id, admin_id, product_id, price, par_price, product_title, qq, amount, hj_order_id)
                    values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(strtotime("now"), $order_id, $_SESSION['usr_id'], $product_id, $price, $par_price, $title, $qq, $amount, $hj_order_id);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }*/
    public function insert_order($order_id, $product_id, $price, $amount, $par_price, $title, $qq, $hj_order_id,$status_info,$game_id){
        $this->sql = "insert into hengjing(addtime, order_id, admin_id, product_id, price, par_price, product_title, qq, amount, hj_order_id,status_info,game_id)
                    values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(strtotime("now"), $order_id, $_SESSION['usr_id'], $product_id, $price, $par_price, $title, $qq, $amount, $hj_order_id,$status_info,$game_id);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
    public function get_all_list($params){
        $this->sql="select * from hengjing WHERE 1=1";
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
        if($params['admin_id']){
            $this->sql = $this->sql." and `admin_id` = ".$params['admin_id'];
        }
        if ($params['game_id'] && is_numeric($params['game_id']) || $params['game_id'] === "0"){
            $this->sql = $this->sql." and `game_id` = ".$params['game_id'];
        }
        if($params['start_time']){
            $this->sql = $this->sql." and  addtime>=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and  addtime<".strtotime($params['end_time']);
        }
        $this->sql=$this->sql." order by id desc";
        $this->doResultList();
        return $this->result;
    }
    public function get_operation_name(){
        $this->sql = "SELECT a.id,a.real_name FROM hengjing h INNER JOIN niuniu.admins a ON
                  h.admin_id=a.id GROUP BY h.admin_id";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from `niuniu`.admins where id =?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_province_list($page){
        $this->limit_sql = 'select * from qb_province where is_del = 0 order by id desc';
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_province_info($province,$id=''){
        $this->sql = "select * from qb_province where is_del=0 and province like '%".$province."%'";
        if($id){
            $this->sql .= " and id != ".$id;
        }
        $this->doResult();
        return $this->result;
    }

    public function insert_province($province){
        $this->sql = "insert into qb_province (province,add_time)values (?,?)";
        $this->params = array($province,time());
        $this->doExecute();
    }

    public function get_province_all(){
        $this->sql = "select * from qb_province where is_del=0";
        $this->doResultList();
        return $this->result;
    }

    public function get_province($id){
        $this->sql = "select * from qb_province where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_province($params){
        $this->sql = "update qb_province set province=? where id=?";
        $this->params = array($params['province'],$params['id']);
        $this->doExecute();
    }

    public function get_ip_session($ip){
        $data = $this->mmc->get("hengjing_".$ip);
        return $data;
    }

    public function set_ip_session($ip,$data){
        $this->mmc->set("hengjing_".$ip,$data,1,600);
    }

    public function get_order_amount($qq,$start_time){
        $this->sql = "select sum(amount) as amount from hengjing where qq = ? and status = 1 or (status = 0 and callback_time is NULL ) and addtime >= ? and addtime <= ?";
        $this->params = array($qq,$start_time,time());
        $this->doResult();
        return $this->result;
    }
}