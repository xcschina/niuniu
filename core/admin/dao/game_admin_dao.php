<?php
COMMON('niuniuDao');
class game_admin_dao extends niuniuDao{
    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_app_list($page,$params){
        $this->limit_sql = "select * from business_apps where is_del=0 ";
        if ($params['app_id']){
            $this->limit_sql .= " AND app_id=".$params['app_id'];
        }
        $this->limit_sql = $this->limit_sql. " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_all_app(){
        $this->sql = "SELECT * FROM business_apps WHERE is_del=0 ORDER BY id DESC";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_all(){
        $this->sql = "SELECT * FROM business_apps WHERE is_del=0 AND chamber_type=0 OR chamber_type=2 ORDER BY id DESC";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_by_appid($app_id){
        $this->sql = "SELECT * FROM business_apps WHERE app_id=? ORDER BY id DESC";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_app($params){
        $this->sql = "INSERT INTO business_apps(app_id,app_name,type,add_time,modify_time,channel,chamber_type,payment_scale,goods_scale)VALUES(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id_add'],$params['app_name_add'],$params['type_add'],time(),time(),$params['channel'],$params['chamber_type'],$params['payment_scale'],$params['goods_scale']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_app_check($app_id,$id){
        $this->sql = "SELECT * FROM business_apps WHERE app_id=? AND id!=? ORDER BY id DESC";
        $this->params = array($app_id,$id);
        $this->doResult();
        return $this->result;
    }

    public function update_app($params){
        $this->sql = "UPDATE business_apps SET app_id=?,app_name=?,type=?,modify_time=?,channel=?,chamber_type=?,payment_scale=?,goods_scale=? WHERE id=?";
        $this->params = array($params['app_id_edit'],$params['app_name_edit'],$params['type_edit'],time(),$params['channel'],$params['chamber_type'],$params['payment_scale'],$params['goods_scale'],$params['id']);
        $this->doExecute();
    }
}