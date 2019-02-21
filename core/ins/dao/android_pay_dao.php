<?php
COMMON('dao','niuniuDao');
class android_pay_dao extends niuniuDao {

	public function __construct() {
		parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
	}

    public function get_app_info($app_id){
        $data = $this->mmc->get("app_info".$app_id);
        if (!$data) {
            $this->sql = "select * from apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_apple_info($app_id,$apple_id){
        $data = $this->mmc->get("app_info_".$apple_id);
        if(!$data){
            $this->sql = "select a.*,a.game_name as app_name,b.web_url from app_ios_pack a left join apps b on a.app_id = b.app_id where a.app_id =? and a.channel=? ";
            $this->params = array($app_id,$apple_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info_".$apple_id,$data,1,3600);
        }
        return $data;
    }

    public function get_apple_id_info($app_id,$apple_id){
        $data = $this->mmc->get("apple_id_info_".$apple_id);
        if(!$data){
            $this->sql = "select * from app_ios_pack  where app_id =? and apple_id=? ";
            $this->params = array($app_id,$apple_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("apple_id_info_".$apple_id,$data,1,3600);
        }
        return $data;
    }

    public function get_super_app_info($app_id){
        $data = $this->mmc->get("super_app_info".$app_id);
        if (!$data) {
            $this->sql = "select * from super_apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("super_app_info".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_super_channel_info($appid,$channel){
//        $data = $this->mmc->get("super_channel_info_".$app_id."_".$channel);
        if (!$data) {
            $this->sql = "select * from channel_apps where super_id= ".$appid." and ch_code= '".$channel."'";
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("super_channel_info_".$app_id."_".$channel, $data, 1,3600);
        }
        return $data;
    }

    public function get_channel_moeny($app_id,$channel){
        $data = $this->mmc->get("channel_moeny_".$app_id."_".$channel);
        if (!$data) {
            $this->sql = "SELECT COUNT(1) as sum from super_orders WHERE app_id=? and channel=? and `status` > 0";
            $this->params = array($app_id,$channel);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("channel_moeny_".$app_id."_".$channel, $data, 1,3600);
        }
        return $data;
    }

    public function get_channel_sum_moeny($app_id,$channel){
        $this->sql = "SELECT sum(pay_money) as sum from super_orders WHERE app_id=? and channel=? and `status` > 0";
        $this->params = array($app_id,$channel);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_good_data($app_id){
        $data = $this->mmc->get("app_goods".$app_id);
        if (!$data) {
            $this->sql = "select * from app_goods where app_id=? and status=1 order by good_price";
            $this->params = array($app_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "app_goods".$app_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_mmc_warn_money($id){
        $data = $this->mmc->get("get_mmc_warn_money_".$id);
        return $data;
    }

    public function set_mmc_warn_money($id,$data){
        $this->mmc->set("get_mmc_warn_money_".$id, $data, 1, 14400);
    }

    public function get_good_info($app_id,$good_id=''){
        $this->sql = "select * from app_goods where app_id=? and status=1  and good_code=? order by good_price";
        $this->params = array($app_id,$good_id);
        $this->doResult();
        return $this->result;
    }

    public function get_super_good_info($app_id,$good_id=''){
        $this->sql = "select * from super_app_goods where app_id=? and status=1  and good_code=? order by good_price";
        $this->params = array($app_id,$good_id);
        $this->doResult();
        return $this->result;
    }

    public function get_good_discount($app_id,$ch){
        $this->sql = "select * from apps_discount_tb where app_id=? and channel=? ";
        $this->params = array($app_id,$ch);
        $this->doResult();
        return $this->result;
    }

    public function get_money_info($app_id,$money_id){
        #$this->sql = "select b.app_name,a.* from app_goods as a inner join apps as b on a.app_id=b.app_id where a.id=? and a.status=1 order by good_price";
        $this->sql = "select b.app_name,a.* from app_goods as a inner join apps as b on a.app_id=b.app_id where a.app_id=? and a.good_code=? and a.status=1 order by good_price";
        $this->params = array($app_id,$money_id);
        $this->doResult();
        return $this->result;
    }

    public function get_super_money_info($app_id,$money_id){
        $this->sql = "select b.app_name,a.* from super_app_goods as a inner join super_apps as b on a.app_id=b.app_id where a.app_id=? and a.good_code=? and a.status=1 order by good_price";
        $this->params = array($app_id,$money_id);
        $this->doResult();
        return $this->result;
    }

    public function create_order($order){
        $this->sql = "insert into orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount, 
                                        pay_money,discount,pay_price,status, buy_time, ip, serv_id, channel,payExpandData,serv_name)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function create_super_order($order){
        $this->sql = "insert into super_orders(app_id, order_id, app_order_id, pay_channel, buyer_id, role_id, product_id, unit_price, title, role_name, amount,
                                        pay_money,discount,pay_price,status, buy_time, ip, serv_id, channel,payExpandData,serv_name)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function create_nnb_order($order){
        $this->sql = "insert into nnb_orders(app_id,order_id,pay_channel,buyer_id,title,pay_money,nnb_num,`rate`,`status`,buy_time,ip,channel)values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_order_success($order_id, $ali_order_id, $buy_email='', $status=1){
        if($status == 1){
            $this->sql = "update orders set status=?, pay_order_id=?,payer=?,pay_time=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, strtotime("now"),$order_id);
        }else{
            $this->sql = "update orders set status=?,pay_order_id=?,payer=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, $order_id);
        }
        $this->doExecute();
    }

    public function update_wap_wx_order_success($order_id, $ali_order_id, $buy_email='', $status=1,$collect_company){
        if($status == 1){
            $this->sql = "update orders set status=?, pay_order_id=?,payer=?,pay_time=?,collect_company=?  where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, strtotime("now"),$collect_company,$order_id);
        }else{
            $this->sql = "update orders set status=?,pay_order_id=?,payer=?,collect_company=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email,$collect_company, $order_id);
        }
        $this->doExecute();
    }

    public function update_ali_order_success($order_id, $ali_order_id, $buy_email='', $status=1,$collect_company){
        if($status == 1){
            $this->sql = "update orders set status=?, pay_order_id=?,payer=?,pay_time=?,collect_company=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, strtotime("now"),$collect_company,$order_id);
        }else{
            $this->sql = "update orders set status=?,pay_order_id=?,payer=?,collect_company=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email,$collect_company, $order_id);
        }
        $this->doExecute();
    }

    public function update_app_order_success($order_id, $ali_order_id, $buy_email='', $status=1){
        $this->sql = "update `66173`.orders set channel_order_id=?,status=?,payer=?,pay_time=? where order_id=?";
        $this->params = array($ali_order_id, $status, $buy_email, strtotime("now"),$order_id);
        $this->doExecute();
    }

    public function update_app_user_info($is_vip,$order){
        $this->sql = "update `66173`.user_info set is_vip=?,last_buy_time=? where user_id=?";
        $this->params = array($is_vip, strtotime("now"),$order['buyer_id']);
        $this->doExecute();
    }

    public function update_super_order_success($order_id, $ali_order_id, $buy_email='', $status=1){
        if($status == 1){
            $this->sql = "update super_orders set status=?, pay_order_id=?,payer=?,pay_time=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, strtotime("now"),$order_id);
        }else{
            $this->sql = "update super_orders set status=?,pay_order_id=?,payer=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, $order_id);
        }
        $this->doExecute();
    }

    public function update_nnb_order_success($order_id, $ali_order_id, $buy_email='', $status=1){
        if($status == 1){
            $this->sql = "update nnb_orders set status=?, pay_order_id=?,payer=?,pay_time=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, strtotime("now"), $order_id);
        }else{
            $this->sql = "update nnb_orders set status=?,pay_order_id=?,payer=? where order_id=?";
            $this->params = array($status, $ali_order_id, $buy_email, $order_id);
        }
        $this->doExecute();
    }

    public function get_order_info($order_id){
        $this->sql = "select * from orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_app_order_info($order_id){
        $this->sql = "select * from `66173`.orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_super_order_info($order_id){
        $this->sql = "select * from super_orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_nnb_order_info($order_id){
        $this->sql = "select * from nnb_orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function update_nnb_order_info($order){
        $this->sql = "update nnb_orders set charge_time=? where order_id=?";
        $this->params = array(strtotime("now"),$order['order_id']);
        $this->doExecute();
    }

    public function update_nnb_pay_info($order_id){
        $this->sql = "update orders set pay_time=?,charge_time=?,status=? where order_id=?";
        $this->params = array(strtotime("now"),strtotime("now"),1,$order_id);
        $this->doExecute();
    }

    public function nd_user_lock($nd_lock,$app_id,$user_id){
        $this->sql = "UPDATE nd_user_info SET nd_lock=? WHERE app_id=? AND user_id=?";
        $this->params = array($nd_lock,$app_id,$user_id);
        $this->doExecute();
    }

    public function update_user_nd($order){
        $this->sql = "update nd_user_info set nd_num=nd_num-? where user_id=? and app_id=? ";
        $this->params = array($order['nd_num'], $order['buyer_id'], $order['app_id']);
        $this->doExecute();

        $this->nd_user_lock(0,$order['app_id'], $order['buyer_id']);
    }

    public function insert_nd_user_revoke_log($param,$do_type,$reason){
        $this->sql = "INSERT INTO nd_operation_log(orders,user_id,app_id,do_type,nd_num,order_type,add_time,remarks)VALUES(?,?,?,?,?,?,?,?)";
        $this->params = array($param['order_id'],$param['buyer_id'],$param['app_id'],$do_type,$param['nd_num'],2,time(),$reason);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_user_niu_coin($user_id){
        $this->sql = "select * from orders where order_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function set_usr_session($key, $data){
        $session_data = $this->mmc->get("mpay-session-".session_id());
        $session_data[$key] = $data;
        $this->mmc->set("mpay-session-".session_id(), $session_data, 1, 300);
    }

    public function get_usr_session($key){
        $session_data = $this->mmc->get("mpay-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }

    //充值log
    public function charge_log($appid, $orderid, $desc, $time, $status=0, $memo='', $expand=''){
        $this->sql = "insert into charge_log(app_id, order_id, status, intro, add_time, memo, payExpandData)values(?,?,?,?,?,?,?)";
        $this->params = array($appid, $orderid, $status, $desc, $time, $memo, $expand);
        $this->doExecute();
        echo $this->LAST_INSERT_ID;
    }

    public function order_list($user_id, $status = -1,$page){
        if($status == -1){
            $this->limit_sql = 'select * from orders where buyer_id=? order by buy_time DESC';
            $this->params = array($user_id);
        }else{
            $this->limit_sql = 'select * from orders where user_id=? and status=? order by buy_time DESC';
            $this->params = array($user_id, $status);
        }
        $this->doLimitResultList($page);
        $data = $this->result;
        return $data;
    }

    public function get_payed_orders(){
        $this->sql = "SELECT b.sdk_charge_url,b.app_key,a.*,c.good_code FROM `orders` as a inner join apps as b on a.app_id=b.app_id 
                      inner join app_goods as c on a.product_id=c.id where a.status=1 order by a.charge_time asc";
        $this->doResultList();
        return $this->result;
    }

    public function get_valid_url($appid,$code){
        $data = $this->mmc->get("valid_url_".$appid."_".$code);
        if($data){
            return $data;
        }else{
            return false;
        }
    }

    public function set_valid_url($appid,$code,$data){
        $this->mmc->set("valid_url_".$appid."_".$code,$data,3600);
    }

    public function get_super_payed_orders(){
        $this->sql = "SELECT b.sdk_charge_url,b.app_key,a.*,c.good_code FROM `super_orders` as a inner join super_apps as b on a.app_id=b.app_id
                      inner join super_app_goods as c on a.product_id=c.id where a.status=1 order by a.charge_time asc";
        $this->doResultList();
        return $this->result;
    }

    public function get_ry_payed_orders($channel){
        $this->sql = "SELECT * from orders WHERE ry_status=0 and `status`= 2 and web_channel in(".$channel.")";
        $this->doResultList();
        return $this->result;
    }

    public function get_ry_array(){
        $this->sql = "SELECT channel from ry_info where is_del= 0";
        $this->doResultList();
        return $this->result;
    }

    public function get_ry_appid($params){
        $this->sql = "SELECT * from ry_info where is_del= 0 and app_id=? and channel=? ";
        $this->params = array($params['app_id'], $params['web_channel']);
        $this->doResult();
        return $this->result;
    }

    public function update_order_status($id, $status){
        $this->sql = "update orders set status=? where id=?";
        $this->params = array($status, $id);
        $this->doExecute();
    }

    public function update_ry_order_status($order, $status){
        $this->sql = "update orders set ry_status=? where id=?";
        $this->params = array($status, $order['id']);
        $this->doExecute();
    }

    public function update_super_order_status($id, $status){
        $this->sql = "update super_orders set status=? where id=?";
        $this->params = array($status, $id);
        $this->doExecute();
    }

    public function update_super_order_charge($id){
        $this->sql = "update super_orders set charge_time=? where id=?";
        $this->params = array(strtotime("now"), $id);
        $this->doExecute();
    }

    public function update_order_charge($id){
        $this->sql = "update orders set charge_time=? where id=?";
        $this->params = array(strtotime("now"), $id);
        $this->doExecute();
    }

    public function get_userapp_channel($user_id, $app_id){
        $this->sql = "select * from user_apptb where userid=? and app_id=? order by id asc limit 0,1";
        $this->params = array($user_id, $app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_list(){
        $this->sql = "select * from orders where ch_type = 0 order by id limit 1000";
        $this->doResultList();
        return $this->result;
    }

    public function get_role_order_list(){
        $this->sql = "select * from orders where ad_id is NULL and `status`= 2 and buy_time >='1542816000' and app_id='6024'  order by id limit 50";
        $this->doResultList();
        return $this->result;
    }

    public function get_ry_callback(){
        $this->sql = "select * from ry_callback where `status`= 0 and addtime >='1542816000' limit 50";
        $this->doResultList();
        return $this->result;
    }

    public function get_device_sid($imei){
        $this->sql = "SELECT SID from stats_device WHERE Imei= ?";
        $this->params = array($imei);
        $this->doResult();
        return $this->result;
    }

    public function get_device_andord_id($Android_id){
        $this->sql = "SELECT SID from stats_device WHERE Android_id= ?";
        $this->params = array($Android_id);
        $this->doResult();
        return $this->result;
    }

    public function up_ry_info($id,$status,$sid){
        $this->sql = "update ry_callback set `status`=?,sid=? where id=?";
        $this->params = array($status,$sid,$id);
        $this->doExecute();
    }

    public function get_role_info($params){
        $this->sql = "select * from stats_user_app_relation where app_id = ? and serv_id = ? and role_id = ?";
        $this->params = array($params['app_id'], $params['serv_id'],$params['role_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_role_money($params){
        $this->sql = "select sum(pay_money) as sum_pay_money from orders where app_id = ? and serv_id = ? and role_id = ? and `status`= 2 and pay_channel in ('1','2')";
        $this->params = array($params['app_id'], $params['serv_id'],$params['role_id']);
        $this->doResult();
        return $this->result['sum_pay_money'];
    }

    public function get_role_platfor_money($params){
        $this->sql = "select sum(pay_money) as sum_pay_money from orders where app_id = ? and serv_id = ? and role_id = ? and `status`= 2 and pay_channel in ('3','6')";
        $this->params = array($params['app_id'], $params['serv_id'],$params['role_id']);
        $this->doResult();
        return $this->result['sum_pay_money'];
    }

    public function add_role_info($params,$role_money,$role_platform_money){
        $this->sql = "insert into stats_user_app_relation(user_id,app_id,serv_id,role_id,pay_money,platform_money,add_time)values(?,?,?,?,?,?,?)";
        $this->params = array($params['buyer_id'],$params['app_id'],$params['serv_id'],$params['role_id'],$role_money,$role_platform_money,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function up_role_info($params,$role_money,$role_platform_money){
        $this->sql = "update stats_user_app_relation set pay_money=?,platform_money=?,act_time=? where id=?";
        $this->params = array($role_money,$role_platform_money,time(), $params['id']);
        $this->doExecute();
    }

    public function up_order_info($order,$status){
        $this->sql = "update orders set ad_id=? where id=?";
        $this->params = array($status, $order['id']);
        $this->doExecute();
    }

    public function get_channel_info($channel,$group_id){
        $data = $this->mmc->get("channel_".$channel."_".$group_id);
        if (!$data) {
            $this->sql = "select * from admins where group_id = ?  and user_code = ?";
            $this->params = array($group_id, $channel);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("channel_".$channel."_".$group_id, $data, 1, 300);
        }
        return $data;
    }

    public function update_order_info($order,$status){
        $this->sql = "update orders set ch_type=? where id=?";
        $this->params = array($status, $order['id']);
        $this->doExecute();
    }

    public function get_nd_user_info($user_id,$app_id){
        $this->sql = "select * from nd_user_info where app_id=? and user_id=? ";
        $this->params = array($app_id,$user_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_order_debug_info(){
        $data = $this->mmc->get("order_debug_info");
        return $data;
    }

    public function add_qq_member_order($data,$appid){
        $this->sql = "insert into qq_member_order(payid,appid, dsid, drid, `uid`, taskid, sign, `status`,`time`, add_time)values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($data['payid'],$appid, $data['dsid'],$data['drid'],$data['uid'], $data['taskid'],$data['sign'],1,$data['time'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_qq_member_order($payid){
        $this->sql = "select * from qq_member_order where payid=?";
        $this->params = array($payid);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function update_qq_member_order($id){
        $this->sql = "update qq_member_order set `status`=?,add_time=? where id=?";
        $this->params = array(2,time(), $id);
        $this->doExecute();
    }

    public function get_super_ch_info($app_id){
        $data = $this->mmc->get("super_ch_info_".$app_id);
        if (!$data) {
            $this->sql = "select * from channel_apps where id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("super_ch_info_".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_ch_by_appid($super_id,$ch_code){
        $this->sql = "select * from channel_apps where super_id=? AND ch_code=?";
        $this->params = array($super_id,$ch_code);
        $this->doResult();
        return $this->result ;
    }

    public function get_channel_app_by_appid($appid){
        $this->sql = "select * from channel_apps where app_id= ? ";
        $this->params = array($appid);
        $this->doResult();
        return $this->result ;
    }

    public function get_user_message($user_id){
        $this->sql = "select * from `66173`.user_relation_tb where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_user_message($order,$integral,$exp){
        $this->sql = "update `66173`.user_relation_tb set integral = ?,exp = ? where user_id = ?";
        $this->params = array($integral,$exp,$order['buyer_id']);
        $this->doExecute();
        memcache_delete($this->mmc,"user_info_".$order['buyer_id']);
    }

    public function insert_integral_log($order){
        $this->sql = "insert into integral_log(user_id,type,integral,add_time,app_id,experience) values(?,?,?,?,?,?)";
        $this->params = array($order['buyer_id'],2,$order['integral'],time(),$order['app_id'],$order['integral']);
        $this->doInsert();
    }

    public function insert_user_message($order){
        $this->sql = "insert into `66173`.user_relation_tb (user_id,integral,exp,add_time)values(?,?,?,?)";
        $this->params = array($order['buyer_id'],$order['integral'],$order['integral'],time());
        $this->doInsert();
    }


    public function get_no_city_list(){
        $this->sql = "select * from `66173`.user_info where is_cmn_city=0 and reg_ip > 0 limit 100";
        $this->doResultList();
        return $this->result;
    }

    public function add_user_city_info($country,$region,$city,$u_id){
        $this->sql = "insert into `66173`.user_city_tb (user_id,country,province,city,add_time)values(?,?,?,?,?)";
        $this->params = array($u_id,$country,$region,$city,time());
        $this->doInsert();
    }

    public function get_user_city_info($user_id){
        $this->sql = "select * from `66173`.user_city_tb where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_is_city($user_id){
        $this->sql = "update `66173`.user_info set is_cmn_city = 1 where user_id = ?";
        $this->params = array($user_id);
        $this->doExecute();
    }

    public function get_ry_ad_info($order){
        $this->sql = "select spreadurl from ry_callback as ry left join ry_extend_tb ext on ry.spreadurl=ext.act_id where ext.apple_id=? and ry.idfa=?";
        $this->params = array($order['web_channel'], $order['idfa']);
        $this->doResult();
        return $this->result;
    }

    public function update_ry_order_ad_id($order, $info){
        $this->sql = "update orders set ad_id=? where id=?";
        $this->params = array($info['spreadurl'], $order['id']);
        $this->doExecute();
    }

    public function get_pay_sid($sid){
        $this->sql = "select * from apple_pay_log where sid = ?";
        $this->params = array($sid);
        $this->doResult();
        return $this->result;
    }

    public function get_pay_idfa($idfa){
        $this->sql = "select * from apple_pay_log where idfa = ?";
        $this->params = array($idfa);
        $this->doResult();
        return $this->result;
    }

    public function get_sid_in_apple_order($sid){
        $data = $this->mmc->get("sid_in_apple_order_".md5($sid));
        if (!$data) {
//            $this->sql = "SELECT id from apple_order WHERE sid_md5=? and `status` = 3 and sandbox = 2";
            $this->sql = "SELECT id from apple_order WHERE sid_md5=? and `status` = 3 ";
            $this->params = array(md5($sid));
            $this->doResult();
            $data = $this->result;
            if($data){
                $this->mmc->set("sid_in_apple_order_".md5($sid), $data, 1,3600);
            }
        }
        return $data;
    }
}
