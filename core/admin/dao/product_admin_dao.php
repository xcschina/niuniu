<?php
COMMON('niuniuDao');
class product_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "apps";
    }

    public function get_product_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name from app_goods as a inner join apps as b on a.app_id=b.app_id where 1=1 ";
        if(!empty($params['rec_type'])){
            $this->limit_sql = $this->limit_sql." and a.rec_type = ".$params['rec_type'];
        }
        if(!empty($params['status']) && is_numeric($params['status']) || $params['status'] ==='0'){
            $this->limit_sql = $this->limit_sql." and a.status = ".$params['status'];
        }
        if(!empty($params['good_type'])){
            $this->limit_sql = $this->limit_sql." and a.good_type = ".$params['good_type'];
        }
        if(!empty($params['app_id'])){
            $this->limit_sql = $this->limit_sql." and a.app_id = ".$params['app_id'];
            $this->limit_sql = $this->limit_sql." order by a.status desc , a.id desc";
        }else{
            $this->limit_sql = $this->limit_sql." order by a.id desc";
        }
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_list(){
        $this->sql="select * from apps";
        $this->doResultList();
        return $this->result;
    }

    public function get_product_info($id){
        $this->sql = "select * from app_goods where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_all_app(){
        $this->sql = "select * from apps order by id desc";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function check_app_id($app_id){
        $this->sql = "select * from app_goods where app_id=?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }
    public function insert_product($params){
        $this->sql = "insert into app_goods(app_id, good_name, good_code, good_unit, good_amount, good_type,good_intro,good_price,status,rec_type,limit_num)values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['good_name'],$params['good_code'],$params['good_unit'],$params['good_amount'],$params['good_type'],
                                $params['good_intro'],$params['good_price'],$params['status'],$params['rec_type'],$params['limit_num']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_product($params, $id){
        $this->sql = "update app_goods set good_name=?,good_code=?,good_unit=?,good_amount=?,good_type=?,good_intro=?,good_price=?,status=?,app_id=?,rec_type=?,limit_num=? where id=?";
        $this->params = array($params['good_name'], $params['good_code'], $params['good_unit'], $params['good_amount'], $params['good_type'],
                                $params['good_intro'], $params['good_price'], $params['status'], $params['app_id'],$params['rec_type'],$params['limit_num'], $id);
        $this->doExecute();
        memcache_delete($this->mmc, "web_app_money".$id);
    }

    public function get_product_list_nolimit($params){
        $this->sql = "select a.*,b.app_name from app_goods as a inner join apps as b on a.app_id=b.app_id where 1=1 ";
        if(!empty($params['app_id'])){
            $this->sql = $this->sql." and a.app_id =".$params['app_id'];
        }
        if(!empty($params['rec_type'])){
            $this->sql = $this->sql." and a.rec_type = ".$params['rec_type'];
        }
        if(!empty($params['status']) && is_numeric($params['status']) || $params['status'] === '0'){
            $this->sql = $this->sql." and a.status = ".$params['status'];
        }
        if(!empty($params['good_type'])){
            $this->sql = $this->sql." and a.good_type = ".$params['good_type'];
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }
    public function check_apps($app_id){
        $this->sql = "select * from apps where app_id=?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }
    public function import_app_goods($import_data){
        $sql = 'INSERT INTO app_goods(good_name,good_code,good_unit,good_amount,good_type,good_intro,good_price,status,app_id,rec_type)VALUES';
        foreach ($import_data as $value){
            $sql .= "('".$value['good_name']."','".$value['good_code']."','".$value['good_unit']."',".$value['good_amount'].",".$value['good_type'].",
                    '".$value['good_intro']."',".$value['good_price'].",".$value['status'].",".$value['app_id'].",".$value['rec_type']."),";
        }
        $this->sql = trim($sql,',');
        $this->params = array();
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

}
