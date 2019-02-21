<?php
COMMON('niuniuDao','baseRedis');
class extend_dao extends niuniuDao{

    public $redisDao;

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redisDao = new baseRedis();
        $this->redisDao->switchDB(5);
        $this->TB_NAME = "admins";
    }

    public function get_list($params,$page){
        $this->limit_sql = "select a.*,b.app_name from cpa_app as a left join apps as b on a.app_id = b.app_id where 1=1";
        if($params['app_id']){
            $this->limit_sql = $this->limit_sql." and a.app_id =".$params['app_id'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.status = ".$params['status'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.type = ".$params['type'];
        }
        if($params['start_time'] ){
            $this->limit_sql = $this->limit_sql." and a.start_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql = $this->limit_sql." and a.end_time <= ".strtotime($params['end_time']);
        }
        if($params['cpa_name']){
            $this->limit_sql = $this->limit_sql." and a.cpa_name like '%".trim($params['cpa_name'])."%'";
        }
        if($params['ch_name']){
            $this->limit_sql = $this->limit_sql." and a.ch_name like '%".$params['ch_name']."%'";
        }
        if($params['ch_code']){
            $this->limit_sql = $this->limit_sql." and a.ch_code ='".$params['ch_code']."'";
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }


    public function get_record_list($params,$page){
        $this->limit_sql = "select a.*,b.app_name from cpa_app as a left join apps as b on a.app_id = b.app_id where 1=1";
        if($params['app_id']){
            $this->limit_sql = $this->limit_sql." and a.app_id =".$params['app_id'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.status = ".$params['status'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.type = ".$params['type'];
        }
        if($params['cpa_name']){
            $this->limit_sql = $this->limit_sql." and a.cpa_name like '%".trim($params['cpa_name'])."%'";
        }
        if($params['ch_name']){
            $this->limit_sql = $this->limit_sql." and a.ch_name like '%".$params['ch_name']."%'";
        }
        if($params['ch_code']){
            $this->limit_sql = $this->limit_sql." and a.ch_code ='".$params['ch_code']."'";
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_click_num($adid,$params){
        $this->sql = "select sum(`num`) as num from cpa_check where ad_id=".$adid;

        if($params['start_time'] ){
            $this->sql = $this->sql." and ad_batch >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and ad_batch <= ".strtotime($params['end_time']);
        }
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_cpa_click_num($adid,$params){
        $this->sql = "select count(*) as num from cpa_mac_tb where adid=".$adid;

        if($params['start_time'] ){
            $this->sql = $this->sql." and add_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and add_time <= ".strtotime($params['end_time']);
        }
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_cpa_callback_num($adid,$params){
        $this->sql = "select count(*) as num from cpa_mac_tb where adid=".$adid;

        if($params['start_time'] ){
            $this->sql = $this->sql." and add_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and add_time <= ".strtotime($params['end_time']);
        }
        $this->sql = $this->sql." and is_install= 1 ";
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_callback_num($adid,$params){
        $this->sql = "select count(DISTINCT(cid)) as num from cpa_install where install_check=2 and adid=".$adid;

        if($params['start_time'] ){
            $this->sql = $this->sql." and add_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and add_time <= ".strtotime($params['end_time']);
        }
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_order_num($app_id,$params){
        $this->sql = "select sum(o.pay_money) as num from orders as o left join cpa_mac_tb as mac on o.idfa=mac.idfa  WHERE o.`status`=2 and mac.install_check=2 and o.app_id=".$app_id;
        if($params['start_time'] ){
            $this->sql = $this->sql." and o.buy_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and o.buy_time <= ".strtotime($params['end_time']);
        }
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_cpa_order_num($app_id,$params){
        $this->sql = "select sum(o.pay_money) as num from orders as o left join cpa_install as mac on o.idfa=mac.idfa  WHERE o.`status`=2 and mac.install_check=2 and o.app_id=".$app_id;
        if($params['start_time'] ){
            $this->sql = $this->sql." and o.buy_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and o.buy_time <= ".strtotime($params['end_time']);
        }
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_an_order_num($app_id,$channel,$params){
        $this->sql = "select sum(pay_money) as num from orders  WHERE `status`=2  and app_id=".$app_id." and channel ='".$channel."'";
        if($params['start_time'] ){
            $this->sql = $this->sql." and buy_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql = $this->sql." and buy_time <= ".strtotime($params['end_time']);
        }
        $this->doResult($page);
        return $this->result['num'];
    }

    public function get_game(){
        $data = $this->mmc->get("extend_game_list");
        if(!$data) {
        $this->sql = "select * from apps where status = 1";
        $this->doResultList();
        $data = $this->result;
        $this->mmc->set("extend_game_list", $data, 1, 600);
        }
        return $data;
    }

    public function insert_extend($params){
        $this->sql = "insert into cpa_app(app_id,cpa_name,ch_name,ch_code,aid,cid,`type`,start_time,end_time,url,`status`,add_time) values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['cpa_name'],$params['ch_name'],$params['ch_code'],$params['aid'],$params['cid'],$params['type'],strtotime($params['start_time']),strtotime($params['end_time']),$params['url'],$params['status'],time());
        $this->doInsert();
        $this->redisDao->delete('cpa_app_list');
    }

    public function get_extend_info($id){
        $this->sql = "select * from cpa_app where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_extend($params){
        $this->sql = "update cpa_app set white_ip=?,app_id=?,cpa_name=?,ch_name=?,ch_code=?,aid=?,cid=?,`type`=?,start_time=?,end_time=?,url=?,`status`=?,up_time=? where id=?";
        $this->params = array($params['white_ip'],$params['app_id'],$params['cpa_name'],$params['ch_name'],$params['ch_code'],$params['aid'],$params['cid'],$params['type'],strtotime($params['start_time']),strtotime($params['end_time']),$params['url'],$params['status'],time(),$params['id']);
        $this->doExecute();
        $this->redisDao->delete('cpa_app_list');
    }

    public function get_check_list($id,$params,$page){
        $this->limit_sql = "select * from cpa_check  where 1=1";
//        if($params['app_id']){
//            $this->limit_sql = $this->limit_sql." and a.app_id =".$params['app_id'];
//        }
//        if($params['status'] && is_numeric($params['status']) || $params['status'] === "0"){
//            $this->limit_sql = $this->limit_sql." and a.status = ".$params['status'];
//        }
//        if($params['type'] && is_numeric($params['type']) || $params['type'] === "0"){
//            $this->limit_sql = $this->limit_sql." and a.type = ".$params['type'];
//        }
//        if($params['cpa_name']){
//            $this->limit_sql = $this->limit_sql." and a.cpa_name like '%".trim($params['cpa_name'])."%'";
//        }
//        if($params['ch_name']){
//            $this->limit_sql = $this->limit_sql." and a.ch_name like '%".$params['ch_name']."%'";
//        }
//        if($params['ch_code']){
//            $this->limit_sql = $this->limit_sql." and a.ch_code ='".$params['ch_code']."'";
//        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_install_num($data){
        $this->sql = "select COUNT(1) as `count` from cpa_install where install_check = 2 and `timestamp`= ? and adid=? ";
        $this->params = array($data['ad_batch'],$data['ad_id']);
        $this->doResult();
        return $this->result['count'];
    }


    public function get_today_num($cpa_id){
        $time = strtotime(date('Y-m-d',time()));
        $key = 'id_'.$cpa_id.'_'.$time;
        $this->redisDao->switchDB(5);
        $getID = $this->redisDao->get($key);
        return $getID;
    }

    public function get_cpa_install($params){
        $this->sql = "select * from cpa_mac_tb where 1=1 ";
        if(!empty($params['imei'])){
            $this->sql = $this->sql." and ( imei='".$params['imei']."' or imei ='".md5($params['imei'])."' ) ";
        }
        if(!empty($params['sid'])){
            $this->sql = $this->sql." and sid ="."'".$params['sid']."'";
        }
        if(!empty($params['android_id'])){
            $this->sql = $this->sql." and android_id ='".$params['android_id']."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_cpa_install_by_id($id){
        $this->sql = "select * from cpa_mac_tb where id =?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function del_cpa_install($id){
        $this->sql = "delete from cpa_mac_tb where id=?";
        $this->params = array($id);
        $this->doExecute();
    }
}