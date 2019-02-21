<?php
COMMON('niuniuDao');
class orders_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "orders";
    }

    public function get_order_list($page, $params){
        $count_sql = "SELECT COUNT(*) as count_no from orders as a inner join apps as b on a.app_id=b.app_id
                          left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid ";
        $this->limit_sql = "select a.*,b.app_name,c.channel as ch from orders as a inner join apps as b on a.app_id=b.app_id
                          left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid ";
        $this->limit_sql .= "where 1=1 ";
        $count_sql .= "where 1=1 ";
        if($params['status']== ''){
            $this->limit_sql .= " and a.status= 2 ";
            $count_sql .= " and a.status= 2 ";
            if($params['start_time']){
                $this->limit_sql .= " and a.pay_time >=".strtotime($params['start_time']);
                $count_sql.= " and a.pay_time >=".strtotime($params['start_time']);
            }
            if($params['end_time']){
                $this->limit_sql .= " and a.pay_time <".strtotime($params['end_time']);
                $count_sql.= " and a.pay_time <".strtotime($params['end_time']);
            }
        }
        if($params['apple_id'] == 1){
            $this->limit_sql .= " and a.web_channel = ''";
            $count_sql .= " and a.web_channel = ''";
        }elseif($params['apple_channel']){
            $this->limit_sql .= " and a.web_channel = '".$params['apple_channel']."'";
            $count_sql .= " and a.web_channel = '".$params['apple_channel']."'";
        }elseif($params['apple_id']){
            $this->limit_sql .= " and a.web_channel = '".$params['apple_id']."'";
            $count_sql .= " and a.web_channel = '".$params['apple_id']."'";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id=".$params['app_id'];
            $count_sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status='".$params['status']."'";
            $count_sql .= " and a.status='".$params['status']."'";
            if($params['start_time']){
                $this->limit_sql .= " and a.pay_time >=".strtotime($params['start_time']);
                $count_sql .= " and a.pay_time >=".strtotime($params['start_time']);
            }
            if($params['end_time']){
                $this->limit_sql .= " and a.pay_time <".strtotime($params['end_time']);
                $count_sql .= " and a.pay_time <".strtotime($params['end_time']);
            }
        }
        if($params['status'] == '0'){
            $this->limit_sql .= " and a.status='".$params['status']."'";
            $count_sql .= " and a.status='".$params['status']."'";
        }
        if($params['ch'] && empty($params['ch_status'])){
            $this->limit_sql .= " and c.channel='".$params['ch']."'";
            $count_sql .= " and c.channel='".$params['ch']."'";
            if($params['start_time']){
                $this->limit_sql .= " and a.buy_time >=".strtotime($params['start_time']);
                $count_sql .= " and a.buy_time >=".strtotime($params['start_time']);
            }
            if($params['end_time']){
                $this->limit_sql .= " and a.buy_time <".strtotime($params['end_time']);
                $count_sql .= " and a.buy_time <".strtotime($params['end_time']);
            }
        }
        if($params['ch_id'] && $_SESSION['group_id'] == '1'){
            $this->limit_sql .= " and b.ch_id=".$params['ch_id'];
            $count_sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['buyer_id']){
            $this->limit_sql .= " and a.buyer_id=".$params['buyer_id'];
            $count_sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['qa'] == "1"){
            $this->limit_sql .= " and a.buyer_id not in('15443','71','5632','13478','13474','164626')";
            $count_sql .= " and a.buyer_id not in('15443','71','5632','13478','13474','164626')";
        }elseif($params['qa'] == "2"){
            $this->limit_sql .= " and a.buyer_id in('15443','71','5632','13478','13474','164626')";
            $count_sql .= " and a.buyer_id in('15443','71','5632','13478','13474','164626')";
        }
        if(!empty($params['guild_code'])){
            $this->limit_sql .= " and c.channel in (".$params['guild_code'].")";
            $count_sql.= " and c.channel in (".$params['guild_code'].")";
        }
        if($_SESSION['group_id']<>1){
            if($_SESSION['group_id'] == 10){
                if($_SESSION['guild_code'] && empty($params['guild_code'])){
                    $this->limit_sql .= " and c.channel in (".$_SESSION['guild_code'].")";
                    $count_sql .= " and c.channel in (".$_SESSION['guild_code'].")";
                }
            }
            if(empty($_SESSION['apps'])){
                $this->limit_sql .= " and a.app_id=''";
                $count_sql .= " and a.app_id=''";
            }else{
                $this->limit_sql .= " and a.app_id in (".$_SESSION['apps'].")";
                $count_sql .= " and a.app_id in (".$_SESSION['apps'].")";
            }
        }
        if(!($_SESSION['usr_id'] =='404' || $_SESSION['usr_id'] =='84')){
            $this->limit_sql .= " and b.payee_ch != 3";
            $count_sql .= " and b.payee_ch != 3";
        }
        if($params['pay_channel']){
            $this->limit_sql .= " and a.pay_channel = ".$params['pay_channel'];
            $count_sql .= " and a.pay_channel = ".$params['pay_channel'];
        }
        if($params['channel'] == '1'){
            $this->limit_sql .= " and ch_type = 1 ";
            $count_sql .= " and ch_type = 1 ";
        }elseif($params['channel'] == '2'){
            $this->limit_sql .= " and ch_type > 1 ";
            $count_sql .= " and ch_type > 1 ";
        }
        if($params['order_id']){
            $this->limit_sql .= " AND a.order_id='".$params['order_id']."'";
            $count_sql .= " AND a.order_id='".$params['order_id']."'";
        }
        if($params['pay_order_id']){
            $this->limit_sql .= " AND a.pay_order_id='".$params['pay_order_id']."'";
            $count_sql .= " AND a.pay_order_id='".$params['pay_order_id']."'";
        }
        if($params['channel_code']){
            $this->limit_sql .= " AND a.channel='".trim($params['channel_code'])."'";
            $count_sql .= " AND a.channel='".trim($params['channel_code'])."'";
        }
        $this->limit_sql = $this->limit_sql." group by a.id order by a.id desc";
//        $this->doLimitResultList($page);
        $this->sql = $count_sql;
        $this->doResult();
        $no = $this->result;
        $this->doLimitResultList_OP($page,$no['count_no']);
        return $this->result;
    }

    public function get_ch_order_list($page,$params,$ch_id){
        $this->limit_sql = "select a.*,b.app_name from orders as a inner join apps as b on a.app_id=b.app_id ";
        $this->limit_sql .= " where b.ch_id=".$ch_id;
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status='".$params['status']."'";
        }else{
            $this->limit_sql .= " and a.status > 0";
        }
        if($params['buyer_id']){
            $this->limit_sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.pay_time <=".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_ch_all_order($params){
        $this->sql = "select a.*,b.app_name from orders as a inner join apps as b on a.app_id=b.app_id ";
        $this->sql .= " where b.ch_id=".$params['ch_id'];
        if($params['app_id']){
            $this->sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->sql .= " and a.status='".$params['status']."'";
        }else{
            $this->sql .= " and a.status > 0";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }


    public function get_order_list_nolimit($params){
        $this->sql = "select a.*,b.app_name,b.payee_ch,c.channel as ch from orders as a inner join apps as b on a.app_id=b.app_id
                          left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid ";
        $this->sql .= "where 1=1 ";
        if($params['status'] == '' ){
            $this->sql .= " and a.status= 2 ";
        }
        if($params['apple_id'] == 1){
            $this->sql .= " and a.web_channel = ''";
        }elseif($params['apple_channel']){
            $this->sql .= " and a.web_channel = '".$params['apple_channel']."'";
        }elseif($params['apple_id']){
            $this->sql .= " and a.web_channel = '".$params['apple_id']."'";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id='".$params['app_id']."'";
        }
        if($params['status']){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['status'] === '0'){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['ch'] && empty($params['ch_status'])){
            $this->sql .= " and c.channel='".$params['ch']."'";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['ch_id'] && $params['ch_id'] !='undefined'){
            $this->sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        if($params['qa'] == "1"){
            $this->sql .= " and a.buyer_id not in('15443','71','5632','13478','13474','164626')";
        }elseif($params['qa'] == "2"){
            $this->sql .= " and a.buyer_id in('15443','71','5632','13478','13474','164626')";
        }
        if(!empty($params['guild_code'])){
            $this->sql .= " and c.channel in (".$params['guild_code'].")";
        }
        if ($_SESSION['group_id'] <> 1) {
            if ($_SESSION['group_id'] <> 6) {
                if ($_SESSION['guild_code'] && empty($params['guild_code'])) {
                    $this->sql .= " and c.channel in (" . $_SESSION['guild_code'] . ")";
                }
                if (empty($_SESSION['apps'])) {
                    $this->sql .= " and a.app_id=''";
                } else {
                    $this->sql .= " and a.app_id in (" . $_SESSION['apps'] . ")";
                }
            }
        }
        if(!($_SESSION['usr_id'] =='404' || $_SESSION['usr_id'] =='84')){
            $this->sql .= " and b.payee_ch != 3";
        }
        if($params['pay_channel']){
            $this->sql .= " and a.pay_channel = ".$params['pay_channel'];
        }
        if($params['channel'] == '1'){
            $this->sql .= " and ch_type = 1 ";
        }elseif($params['channel'] == '2'){
            $this->sql .= " and ch_type > 1 ";
        }
        if($params['order_id']){
            $this->sql .= " AND a.order_id='".$params['order_id']."'";
        }
        if($params['pay_order_id']){
            $this->sql .= " AND a.pay_order_id='".$params['pay_order_id']."'";
        }
        if($params['channel_code']){
            $this->sql .= " AND a.channel='".trim($params['channel_code'])."'";
        }
        $this->sql = $this->sql." group by a.id order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_sum_money($params,$ch_id){
        $this->sql = "select sum(a.pay_money) as money from orders as a inner join apps as b on a.app_id=b.app_id ";
        $this->sql .= " where b.ch_id=".$ch_id;
        if($params['app_id']){
            $this->sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->sql .= " and a.status='".$params['status']."'";
        }else{
            $this->sql .= " and a.status > 0";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.pay_time <=".strtotime($params['end_time']);
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }


    public function get_sum_money($params){
        $this->sql = "select sum(money) as money from (select a.pay_money as money from orders as a inner join apps as b on a.app_id=b.app_id
                          left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid ";
        $this->sql .= "where 1=1 ";
        if($params['status'] == ''){
            $this->sql .= " and a.status= 2 ";
        }
        if($params['apple_id'] == 1){
            $this->sql .= " and a.web_channel = ''";
        }elseif($params['apple_channel']){
            $this->sql .= " and a.web_channel = '".$params['apple_channel']."'";
        }elseif($params['apple_id']){
            $this->sql .= " and a.web_channel = '".$params['apple_id']."'";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['status'] === '0'){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['ch'] && empty($params['ch_status'])){
            $this->sql .= " and c.channel='".$params['ch']."'";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['ch_id'] && $_SESSION['group_id'] == '1'){
            $this->sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        if($params['qa'] == "1"){
            $this->sql .= " and a.buyer_id not in('15443','71','5632','13478','13474','164626')";
        }elseif($params['qa'] == "2"){
            $this->sql .= " and a.buyer_id in('15443','71','5632','13478','13474','164626')";
        }
        if(!empty($params['guild_code'])){
            $this->sql .= " and c.channel in (".$params['guild_code'].")";
        }
        if($_SESSION['group_id']<>1){
            if($_SESSION['group_id'] == 10){
                if($_SESSION['guild_code'] && empty($params['guild_code'])){
                    $this->sql .= " and c.channel in (".$_SESSION['guild_code'].")";
                }
            }
            if(empty($_SESSION['apps'])){
                $this->sql .= " and a.app_id=''";
            }else{
                $this->sql .= " and a.app_id in (".$_SESSION['apps'].")";
            }
        }
        if($params['pay_channel']){
            $this->sql .= " and a.pay_channel = ".$params['pay_channel'];
        }
        if($params['channel'] == '1'){
            $this->sql .= " and ch_type = 1 ";
        }elseif($params['channel'] == '2'){
            $this->sql .= " and ch_type > 1 ";
        }
        if(!($_SESSION['usr_id'] =='404' || $_SESSION['usr_id'] =='84')){
            $this->sql .= " and b.payee_ch != 3";
        }
        if($params['order_id']){
            $this->sql .= " AND a.order_id='".$params['order_id']."'";
        }
        if($params['pay_order_id']){
            $this->sql .= " AND a.pay_order_id='".$params['pay_order_id']."'";
        }
        if($params['channel_code']){
            $this->sql .= " AND a.channel='".trim($params['channel_code'])."'";
        }
        $this->sql = $this->sql." group by a.id) as a";
        $this->doResultList();
        return $this->result;
    }

    public function update_app($app, $id){
        $this->sql = "update apps set app_name=?,status=?,lastupdate=?,app_icon=?,sdk_order_url=?,sdk_charge_url=? where id=?";
        $this->params = array($app['app_name'], $app['status'], strtotime("now"), $app['app_icon'], $app['sdk_order_url'], $app['sdk_charge_url'], $id);
        $this->doExecute();
    }
    
    public function insert_app($app, $app_key){
        $this->sql = "insert into apps(app_id, app_key, app_name, status, add_time, sdk_order_url, sdk_charge_url)values(?,?,?,?,?,?,?)";
        $this->params = array($app['app_id'], $app_key,$app['app_name'], $app['status'], strtotime("now"), $app['sdk_order_url'], $app['sdk_charge_url']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_app_icon($img, $id, $app_key){
        $this->sql = "update apps set app_icon=?,app_key=? where id=?";
        $this->params = array($img, $app_key, $id);
        $this->doExecute();
    }

    public function check_app_id($app_id){
        $this->sql = "select * from apps where app_id=?";
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_list(){
        $this->sql = "select * from admins where group_id=10";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_info(){
        $this->sql = "select * from channel_tb order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_code($user_id){
        $this->sql = "select * from admins where group_id=10 and (id =? or p1=? or p2=?)";
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_code_info($user_code){
        $this->sql = "select user_code,p1,p2 from admins where user_code = ?";
        $this->params = array($user_code);
        $this->doResult();
        return $this->result;
    }

    public function get_code_info($p){
        $this->sql = "select user_code from admins where id = ?";
        $this->params = array($p);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info_by_code($code){
        $this->sql = "select * from admins where group_id=10 and user_code='".$code."'";
        $this->doResult();
        return $this->result;
    }

    public function get_niu_log($page,$params){
        $this->limit_sql = "select * from `66173`.nnb_log where 1=1";
        if($params['user_id']){
        $this->limit_sql = $this->limit_sql." and user_id = ".$params['user_id'];
        }
        if($params['order_id']){
            $this->limit_sql = $this->limit_sql." and order_id = '".$params['order_id']."'";
        }
        if($params['do']){
            $this->limit_sql = $this->limit_sql." and do = ".$params['do'];
        }
        if($params['from']){
            $this->limit_sql = $this->limit_sql." and `from` = ".$params['from'];
        }
        if($params['ip']){
            $this->limit_sql = $this->limit_sql." and ip = '".$params['ip']."'";
        }
        if($params['start_time']){
            $this->limit_sql = $this->limit_sql." and add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql = $this->limit_sql." and add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql." order by add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_nnb_order($params,$page){
        $this->limit_sql = "select o.*,app.channel as ch from nnb_orders as o left join user_apptb as app on o.app_id=app.app_id and o.buyer_id=app.userid  where o.status<>0 ";
        if(!empty($params['order_id'])){
            $this->limit_sql = $this->limit_sql." and (o.order_id ='".$params['order_id']."' or o.pay_order_id ='".$params['order_id']."')";
        }
        if(!empty($params['channel'])){
            $this->limit_sql = $this->limit_sql." and o.reg_channel ='".$params['channel']."'";
        }
        if(!empty($params['user_code'])){
            $this->limit_sql = $this->limit_sql." and o.channel = '".$params['user_code']."'";
        }
        if(!empty($params['app_id'])){
            $this->limit_sql = $this->limit_sql." and o.app_id = ".$params['app_id'];
        }
        if(!empty($params['buyer_id'])){
            $this->limit_sql = $this->limit_sql." and o.buyer_id = ".$params['buyer_id'];
        }
        if(!empty($params['start_time'])){
            $this->limit_sql = $this->limit_sql." and o.buy_time >= ".strtotime($params['start_time']);
        }
        if(!empty($params['end_time'])){
            $this->limit_sql = $this->limit_sql." and o.buy_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql." order by o.buy_time desc";
        $this->doLimitResultList($page);
        return $this->result;

    }

    public function  get_error_list($page, $params){
        $this->limit_sql = "select a.*,b.app_name,c.channel as ch,d.user_code from orders as a inner join apps as b on a.app_id=b.app_id
                              left join user_apptb as c on a.app_id=c.app_id and a.buyer_id=c.userid left join admins as d on a.channel = d.user_code where a.status = 1";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['ch']){
            $this->limit_sql .= " and c.channel='".$params['ch']."'";
        }
        if($params['ch_id'] && $_SESSION['group_id'] == '1'){
            $this->limit_sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['buyer_id']){
            $this->limit_sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        if(!empty($params['guild_code'])){
            $this->limit_sql .= " and c.channel in (".$params['guild_code'].")";
        }
        if($_SESSION['group_id']<>1){
            if($_SESSION['guild_code'] && empty($params['guild_code'])){
                $this->limit_sql .= " and c.channel in (".$_SESSION['guild_code'].")";
            }
            if(empty($_SESSION['apps'])){
                $this->limit_sql .= " and a.app_id=''";
            }else{
                $this->limit_sql .= " and a.app_id in (".$_SESSION['apps'].")";
            }
        }
        if($params['pay_channel']){
            $this->limit_sql .= " and a.pay_channel = ".$params['pay_channel'];
        }
        if($params['channel'] == '1'){
            $this->limit_sql .= " and d.user_code is null";
        }elseif($params['channel'] == '2'){
            $this->limit_sql .= " and d.user_code is not null and d.group_id = 10 ";
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_order($id){
        $this->sql = "update orders set status = 2 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function insert_operator($operator_id,$order_id,$ip){
        $this->sql = "insert into operator_log(ip,operator_id,order_id,add_time,status) values(?,?,?,?,?)";
        $this->params = array($ip,$operator_id,$order_id,time(),'3');
        $this->doInsert();
    }

    public function get_apple_order_list($page, $params){
        $this->limit_sql = "select a.*,b.app_name from apple_order as a inner join apps as b on a.app_id=b.app_id ";
        $this->limit_sql .= "where 1=1 ";
        if($params['status']== ''){
            $this->limit_sql .= " and a.status > 0 ";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status='".$params['status']."'";
        }
        if($params['status'] === '0'){
            $this->limit_sql .= " and a.status='".$params['status']."'";
        }
        if($params['ch_id'] && $_SESSION['group_id'] == '1'){
            $this->limit_sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['buyer_id']){
            $this->limit_sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        if($params['sandbox'] && is_numeric($params['sandbox']) || $params['sandbox'] === '0'){
            $this->limit_sql .= " and a.sandbox ='" .$params['sandbox']."'";
        }
        if($_SESSION['group_id']<>1){
            if(empty($_SESSION['apps'])){
                $this->limit_sql .= " and a.app_id=''";
            }else{
                $this->limit_sql .= " and a.app_id in (".$_SESSION['apps'].")";
            }
        }
        if($params['apple_id'] == 1){
            $this->limit_sql .= " and a.apple_id = ''";
        }elseif($params['apple_channel']){
            $this->limit_sql .= " and a.apple_id = '".$params['apple_channel']."'";
        }elseif($params['apple_id']){
            $this->limit_sql .= " and a.apple_id = '".$params['apple_id']."'";
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id='".$params['order_id']."'";
        }
        if($params['apple_order_id']){
            $this->limit_sql .= " and a.apple_order_id='".$params['apple_order_id']."'";
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_sum_apple_money($params){
        $this->sql = "select sum(a.pay_money) as money from apple_order as a inner join apps as b on a.app_id=b.app_id ";
        $this->sql .= "where 1=1 ";
        if($params['status'] == ''){
            $this->sql .= " and a.status > 0 ";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] ==="0"){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['ch_id'] && $_SESSION['group_id'] == '1'){
            $this->sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        if($params['sandbox'] && is_numeric($params['sandbox']) || $params['sandbox'] === '0'){
            $this->sql .= " and a.sandbox =" .$params['sandbox'];
        }
        if($_SESSION['group_id']<>1){
            if(empty($_SESSION['apps'])){
                $this->sql .= " and a.app_id=''";
            }else{
                $this->sql .= " and a.app_id in (".$_SESSION['apps'].")";
            }
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_order_list_nolimit($params){
        $this->sql = "select a.*,b.app_name,b.payee_ch from apple_order as a inner join apps as b on a.app_id=b.app_id ";
        $this->sql .= "where 1=1 ";
        if($params['status'] == ''){
            $this->sql .= " and a.status > 0 ";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id='".$params['app_id']."'";
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] ==="0"){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id=".$params['buyer_id'];
        }
        if($params['ch_id'] && $params['ch_id'] !='undefined'){
            $this->sql .= " and b.ch_id=".$params['ch_id'];
        }
        if($params['start_time']){
            $this->sql .= " and a.pay_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.pay_time <".strtotime($params['end_time']);
        }
        if($params['sandbox'] && is_numeric($params['sandbox']) || $params['sandbox'] === '0'){
            $this->sql .= " and a.sandbox =" .$params['sandbox'];
        }
        if($_SESSION['group_id']<>1){
            if(empty($_SESSION['apps'])){
                $this->sql .= " and a.app_id=''";
            }else{
                $this->sql .= " and a.app_id in (".$_SESSION['apps'].")";
            }
        }
        if($params['apple_id'] == 1){
            $this->sql .= " and a.apple_id = ''";
        }elseif($params['apple_channel']){
            $this->sql .= " and a.apple_id = '".$params['apple_channel']."'";
        }elseif($params['apple_id']){
            $this->sql .= " and a.apple_id = '".$params['apple_id']."'";
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id='".$params['order_id']."'";
        }
        if($params['apple_order_id']){
            $this->sql .= " and a.apple_order_id='".$params['apple_order_id']."'";
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_info($app_id){
        $this->sql = 'select * from apps where app_id =?';
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_apple_list($app_id){
        $this->sql = 'select * from app_ios_pack where app_id = ?';
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_info($apple_id){
        $this->sql = 'select channel from app_ios_pack where apple_id = ?';
        $this->params = array($apple_id);
        $this->doResult();
        return $this->result;
    }

    public function get_qq_member_list($page,$params){
        $this->limit_sql = 'select a.*,b.app_name from qq_member_order a left join apps b on a.appid=b.app_id where 1=1';
        if($params['app_id']){
            $this->limit_sql .= ' and a.appid ='.$params['app_id'];
        }
        if($params['dsid']){
            $this->limit_sql .= ' and a.dsid ='.$params['dsid'];
        }
        if($params['drid']){
            $this->limit_sql .= ' and a.drid ='.$params['drid'];
        }
        if($params['uid']){
            $this->limit_sql .= ' and a.uid ='.$params['uid'];
        }
        if($params['taskid']){
            $this->limit_sql .= ' and a.taskid ='.$params['taskid'];
        }
        if($params['status']){
            $this->limit_sql .= ' and a.status ='.$params['status'];
        }
        $this->limit_sql .= ' order by a.add_time desc';
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_list(){
        $this->sql = "select * from apps where status=1";
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_orders_correct_list($page,$params){
        $this->limit_sql = 'SELECT ao.*,ac.operation_type,ac.operation_time,ac.operation_id FROM apple_orders_correct ac INNER JOIN apple_order ao ON ac.apple_orders_id=ao.id AND 1=1 ';
        if ($params['buyer_id']){
            $this->limit_sql .= ' AND ao.buyer_id='.$params['buyer_id'];
        }
        if ($params['order_id']){
            $this->limit_sql .= ' AND (ao.order_id=\''.$params['order_id'].'\' OR ao.cp_order_id=\''.$params['order_id'].'\' OR ao.apple_order_id=\''.$params['order_id'].'\')';
        }
        if ($params['admin_id']){
            $this->limit_sql .= ' AND ac.operation_id='.$params['admin_id'];
        }
        if ($params['start_time']){
            $this->limit_sql .= ' AND ac.operation_time>='.strtotime($params['start_time']);
        }
        if ($params['end_time']){
            $this->limit_sql .= ' AND ac.operation_time<'.strtotime($params['end_time']);
        }
        $this->limit_sql .= ' ORDER BY ac.operation_time DESC';
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_apple_order_fail($params){
        $this->sql = 'SELECT * FROM apple_order WHERE 1=1 ';
        if (isset($params['id']) && !empty($params['id'])){
            $this->sql .= ' AND id='.$params['id'];
        }
        if (isset($params['order_id']) && !empty($params['order_id'])){
            $params['order_id'] = trim($params['order_id']);
            $this->sql .= ' AND (order_id=\''.$params['order_id'].'\' OR cp_order_id=\''.$params['order_id'].'\' OR apple_order_id=\''.$params['order_id'].'\')';
        }
        $this->sql .= ' AND status=0 LIMIT 1';
        $this->doResult();
        return $this->result;
    }

    public function update_apple_order_fail($pay_time,$charge_time,$id){
        $this->sql = 'UPDATE apple_order SET sandbox=?,status=?,pay_time=?,charge_time=? WHERE id=?';
        $this->params = array(2,3,$pay_time,$charge_time,$id);
        $this->doExecute();
    }

    public function insert_apple_orders_correct($id,$type){
        $this->sql = 'INSERT INTO apple_orders_correct(apple_orders_id,operation_type,operation_time,operation_id)VALUES(?,?,?,?)';
        $this->params = array($id,$type,time(),$_SESSION['usr_id']);
        $this->doInsert();
    }

    public function get_admins_info_by_id($user_id){
        $this->sql = 'SELECT pay_pwd FROM admins WHERE is_del=0 AND id=? LIMIT 1';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

}