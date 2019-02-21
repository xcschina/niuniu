<?php
COMMON('niuniuDao');
class super_product_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "apps";
    }

    public function get_product_list($page,$appid){
        $this->limit_sql = "select a.*,b.app_name from super_app_goods as a inner join super_apps as b on a.app_id=b.app_id where 1=1 ";
        if(!empty($appid)){
            $this->limit_sql = $this->limit_sql." and a.app_id = ".$appid;
            $this->limit_sql = $this->limit_sql." order by a.status desc , a.id desc";
        }else{
            $this->limit_sql = $this->limit_sql." order by a.id desc";
        }
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_list(){
        $this->sql="select * from super_apps";
        $this->doResultList();
        return $this->result;
    }

    public function get_product_info($id){
        $this->sql = "select * from super_app_goods where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_all_app(){
        $this->sql = "select * from super_apps order by app_id desc";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }
//
//    public function check_app_id($app_id){
//        $this->sql = "select * from app_goods where app_id=?";
//        $this->params = array($app_id);
//        $this->doResult();
//        return $this->result;
//    }

    public function insert_product($params){
        $this->sql = "insert into super_app_goods(app_id, good_name, good_code, good_unit, good_amount, good_type,good_intro,good_price,status)values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['good_name'],$params['good_code'],$params['good_unit'],$params['good_amount'],$params['good_type'],
                                $params['good_intro'],$params['good_price'],$params['status']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_product($params, $id){
        $this->sql = "update super_app_goods set good_name=?,good_code=?,good_unit=?,good_amount=?,good_type=?,good_intro=?,good_price=?,status=?,app_id=? where id=?";
        $this->params = array($params['good_name'], $params['good_code'], $params['good_unit'], $params['good_amount'], $params['good_type'],
                                $params['good_intro'], $params['good_price'], $params['status'], $params['app_id'], $id);
        $this->doExecute();
    }

    public function get_product_list_nolimit($app_id){
        $this->sql = "select a.*,b.app_name from super_app_goods as a inner join super_apps as b on a.app_id=b.app_id where 1=1 ";
        if(!empty($app_id)){
            $this->sql = $this->sql." and a.app_id =".$app_id;
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function check_apps($app_id){
        $this->sql = "select * from super_apps where app_id=?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function import_super_app_goods($params){
        $this->sql = "insert into super_app_goods(good_name,good_code,good_unit,good_amount,good_type,good_intro,good_price,status,app_id)values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['good_name'],$params['good_code'],$params['good_unit'],$params['good_amount'],$params['good_type'],$params['good_intro'],$params['good_price'],$params['status'],$params['app_id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_relation_list($page,$ch_code){
        $this->limit_sql = "select a.*,s.app_name,c.channel,g.good_name from super_goods_channel a left join super_apps s on a.app_id=s.app_id left join channel_apps c on a.ch_id=c.id left join super_app_goods g on a.goods_id=g.id where 1=1";
        if($ch_code){
            $this->limit_sql .= " and a.ch_code='".$ch_code."'";
        }
        $this->limit_sql .= " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_channel_app_list(){
        $this->sql = "select a.* from super_apps a left join channel_apps c on a.app_id=c.super_id where a.app_id=c.super_id GROUP BY a.app_id";
        $this->doResultList();
        return $this->result;
    }

    public function get_goods_list($app_id){
        $this->sql = "select * from super_app_goods where app_id = ?";
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_channel_list($app_id){
        $this->sql = "select * from channel_apps where super_id=? group by ch_code";
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_relation($params){
        $this->sql = "select * from super_goods_channel where app_id=? and goods_id=? and ch_code=? and is_pub=0 ";
        if($params['id']){
            $this->sql .= ' and id !='.$params['id'];
        }
        $this->params = array($params['app_id'],$params['goods_id'],$params['ch_code']);
        $this->doResult();
        return $this->result;
    }

    public function insert_goods_channel($params){
        $this->sql = "insert into super_goods_channel(app_id,goods_id,ch_id,add_time,ch_code,channel_goods) values(?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['goods_id'],$params['channel_id'],time(),$params['ch_code'],$params['channel_goods']);
        $this->doInsert();
    }

    public function get_channel_all_list(){
        $this->sql = "select * from channel_apps group by ch_code";
        $this->doResultList();
        return $this->result;
    }

    public function get_channel_info($ch_id){
        $this->sql = "select ch_code from channel_apps where id=?";
        $this->params = array($ch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_relation_info($id){
        $this->sql = "select * from super_goods_channel where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_goods_channel($params){
        $this->sql = " update super_goods_channel set goods_id=?,ch_id=?,app_id=?,ch_code=?,is_pub=?,channel_goods=? where id=?";
        $this->params = array($params['goods_id'],$params['channel_id'],$params['app_id'],$params['ch_code'],$params['is_pub'],$params['channel_goods'],$params['id']);
        $this->doExecute();
    }

    public function get_relation_all_list($ch_code){
        $this->sql = "select a.*,s.app_name,c.channel,g.good_name from super_goods_channel a left join super_apps s on a.app_id=s.app_id left join channel_apps c on a.ch_id=c.id left join super_app_goods g on a.goods_id=g.id where 1=1";
        if($ch_code){
            $this->sql .= " and a.ch_code='".$ch_code."'";
        }
        $this->sql .= " order by id desc";
        $this->doResultList();
        return $this->result;
    }
}
