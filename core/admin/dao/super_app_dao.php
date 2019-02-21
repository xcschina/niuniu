<?php
COMMON('niuniuDao');
class super_app_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "super_apps";
    }

    public function get_app_list($page){
        $this->limit_sql="select * from super_apps";
        $this->limit_sql=$this->limit_sql." order by app_id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_all_app_list(){
        $this->sql="select * from super_apps";
        $this->sql=$this->sql." order by app_id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_app_list($page,$params){
        $this->limit_sql = "select * from channel_apps where 1=1";
        if($params['super_id']){
            $this->limit_sql .= " and super_id = ".$params['super_id'];
        }
        if($params['ch_code']){
            $this->limit_sql .= " and ch_code = '".$params['ch_code']."'";
        }
        $this->limit_sql .= " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_ch_all(){
        $this->sql = "select channel,ch_code from channel_apps group by ch_code";
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_open_app_list($page){
        $this->limit_sql="select * from channel_apps where is_open = '1' ";
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_channel_list(){
        $this->sql = "select * from channel_tb order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_count_moeny($param){
        $this->sql = "select sum(pay_money) as sum_money from super_orders where app_id=? and channel=? and `status` > 1";
        $this->params = array($param['super_id'],$param['ch_code']);
        $this->doResult();
        return $this->result;
    }

    public function insert_channel($params){
        $this->sql = "insert into channel_tb(`name`) values(?)";
        $this->params = array($params['name']);
        $this->doInsert();
    }

    public function get_super_app_info($app_id){
        $this->sql="select * from super_apps where app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_ch_app_info($id){
        $this->sql = "select a.*,b.app_name as game_name from channel_apps as a left join super_apps as b on a.super_id = b.app_id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql="select * from admins where id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_ch_ratio($ratio,$id){
        $this->sql = "update channel_apps set ratio=? where id=?";
        $this->params = array($ratio, $id);
        $this->doExecute();
    }

    public function insert_ch_app_ratio_log($id,$dd_id,$ratio,$old_ratio,$operator_id){
        $this->sql = "INSERT INTO ch_app_ratio_log(parant_id,dd_id,ratio,old_ratio,operator_id,add_time)VALUES(?,?,?,?,?,?)";
        $this->params = array($id,$dd_id,$ratio,$old_ratio,$operator_id,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_super_app_list(){
        $this->sql="select * from super_apps order by app_id desc";
        $this->doResultList();
        return $this->result;
    }

    public function insert_app($app, $app_key){
        $this->sql = "insert into super_apps(app_key, app_name, app_type, status, add_time, sdk_charge_url)values(?,?,?,?,?,?)";
        $this->params = array($app_key, $app['app_name'], $app['app_type'], $app['status'], strtotime("now"), $app['sdk_charge_url']);
        $this->doInsert();
        $this->mmc->delete("all_super_apps");
        return $this->LAST_INSERT_ID;
    }

    public function get_ch_app($ch_code,$super_id){
        $this->sql = "select * from channel_apps where ch_code =? and super_id = ?";
        $this->params = array($ch_code,$super_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_ch_app($data){
        $this->sql = "insert into channel_apps(app_id,app_key,channel,super_id,ch_code,app_name,status,add_time,ch_charge_url)values(?,?,?,?,?,?,?,?,?)";
        $this->params = array($data['app_id'],$data['app_key'], $data['channel'],$data['super_id'], $data['ch_code'], $data['app_name'], $data['status'], strtotime("now"), $data['ch_charge_url']);
        $this->doInsert();
        $this->mmc->delete("all_channel_apps");
        return $this->LAST_INSERT_ID;
    }

    public function update_app($app, $app_id){
        $this->sql = "update super_apps set app_name=?,app_type=?,status=?,sdk_charge_url=?,version=? where app_id=?";
        $this->params = array($app['app_name'], $app['app_type'], $app['status'],$app['sdk_charge_url'],$app['version'], $app_id);
        $this->doExecute();
        $this->mmc->delete("super_app_info".$app_id);
    }

    public function update_ch_app($app, $id){
        $this->sql = "update channel_apps set channel=?,ch_code=?,app_name=?,super_id=?,ch_charge_url=?,status=?,`param1`=?,`param2`=?,version=?,version_url=? where id=?";
        $this->params = array($app['channel'],$app['ch_code'],$app['app_name'],$app['super_id'],$app['ch_charge_url'],$app['status'],$app['param1'],$app['param2'],$app['version'],$app['version_url'], $id);
        $this->doExecute();
        $this->mmc->delete("ch_app_info".$id);
    }

    public function update_ch_app_info($params,$info){
        $this->sql = "update channel_apps set best_money = ?,is_open = ? where id=?";
        $this->params = array($params['best_money'],$params['is_open'],$params['id']);
        $this->doExecute();
        $this->mmc->delete("super_app_info_".$info['app_id']."_".$info['channel']);
    }

    public function update_ch_warn_money($info,$money){
        $this->sql = "update channel_apps set warn_money = ? where id=?";
        $this->params = array($money,$info['id']);
        $this->doExecute();
        $this->mmc->delete("super_app_info_".$info['app_id']."_".$info['channel']);
    }

    public function update_ch_info_moblie($info,$data){
        $this->sql = "update channel_apps set warn_money = ?,mobile=? where id=?";
        $this->params = array($data['warn_money'],$data['mobile'],$info['id']);
        $this->doExecute();
        $this->mmc->delete("super_app_info_".$info['app_id']."_".$info['channel']);
    }

    public function get_ch_money_log($hash){
        $this->sql="select * from channel_money_log where hash = ?";
        $this->params = array($hash);
        $this->doResult();
        return $this->result;
    }

    public function add_channel_moeny_log($app_info,$params,$img,$orders_id,$new_total){
        $this->sql = "INSERT INTO channel_money_log(ch_id,operation_id,pay_money,ratio,old_total,new_total,order_id,hash,img,add_time,remarks)VALUES(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['id'],$_SESSION['usr_id'],$params['amount'],$params['ch_ratio'],$app_info['recharge_count'],$new_total,$orders_id,$params['page_hash'],$img,time(),$params['remarks']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_ch_info($info,$new_total){
        $this->sql = "update channel_apps set recharge_count = ? where id=?";
        $this->params = array($new_total,$info['id']);
        $this->doExecute();
        $this->mmc->delete("super_app_info_".$info['app_id']."_".$info['channel']);
    }

    public function update_app_notice($app, $id){
        $this->sql = "update channel_apps set notice=?,notice_status=? where id=?";
        $this->params = array($app['notice'], $app['notice_status'], $id);
        $this->doExecute();
    }

    public function version_update($app, $id){
        $this->sql = "update channel_apps set version=?,up_title=?,up_desc=?,up_status=? where id=?";
        $this->params = array($app['version'],$app['up_title'],$app['up_desc'],$app['up_status'], $id);
        $this->doExecute();
    }
}