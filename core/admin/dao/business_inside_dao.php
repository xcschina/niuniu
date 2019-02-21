<?php
COMMON('niuniuDao','randomUtils');
class business_inside_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_group_order_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from business_group_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.user_id = d.id where a.is_del=0";
        if($user_id){
            $this->limit_sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        if($params['order_type']){
            $this->limit_sql .= " and a.order_type = ".$params['order_type'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_group_order($id,$status){
        $this->sql = " update business_group_orders set status = ? where id = ?";
        $this->params = array($status,$id);
        $this->doExecute();
    }

    public function get_user_list($user_id){
        $this->sql = "select id from admins where p1 = ? or p2 = ?";
        $this->params = array($user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_app_list(){
        $this->sql = "select * from business_apps where chamber_type=1 or chamber_type = 2";
        $this->doResultList();
        return $this->result;
    }

    public function get_service_list($app_id){
        $this->sql = "select * from business_services where app_id = ?";
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from admins where id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_configure(){
        $this->sql = "select * from business_configure";
        $this->doResult();
        return $this->result;
    }

    public function insert_configure($params){
        $this->sql = "insert into business_configure (vip5,vip10,vip12,add_time) values(?,?,?,?)";
        $this->params = array($params['vip5'],$params['vip10'],$params['vip12'],time());
        $this->doInsert();
    }

    public function update_configure($params,$id){
        $this->sql = "update business_configure set vip5=?,vip10=?,vip12=? where id=?";
        $this->params =array($params['vip5'],$params['vip10'],$params['vip12'],$id);
        $this->doExecute();
    }

    public function insert_operation_log($user_id,$desc,$remarks,$status){
        $this->sql = "insert into admin_operation_log(operation_id,`desc`,remarks,add_time,status)values(?,?,?,?,?)";
        $this->params = array($user_id,$desc,$remarks,time(),$status);
        $this->doInsert();
    }

    public function insert_group_order($params,$user_id,$time=''){
        $this->sql = "insert into business_group_orders(order_id,add_time,app_id,service_id,pay_mode,in_money,exit_depot,buy_name,`desc`,user_id,token_scale,order_types,loss_num) values (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        if($time){
            $add_time = strtotime($time);
        }else{
            $add_time = time();
        }
        $this->params = array($params['order_id'],$add_time,$params['app_id'],$params['service_id'],$params['pay_mode'],$params['in_money'],$params['exit_depot'],$params['buy_name'],$params['desc'],$user_id,$params['token_scale'],$params['order_type'],$params['loss_num']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_group_order_info($params){
        $this->sql = "update business_group_orders set app_id=?,service_id=?,pay_mode=?,in_money=?,exit_depot=?,buy_name=?,`desc`=?,token_scale=?,order_types=?,loss_num=? where id =?";
        $this->params = array($params['app_id'],$params['service_id'],$params['pay_mode'],$params['in_money'],$params['exit_depot'],$params['buy_name'],$params['desc'],$params['token_scale'],$params['order_type'],$params['loss_num'],$params['id']);
        $this->doExecute();
    }

    public function get_app_info($app_id){
        $this->sql = "select * from business_apps where app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_group_day_order($params,$status,$user_id){
        $this->sql = "select * from business_orders_collect where channel = ? and order_time = ? and status = ? and group_id = ? and app_id = ? and is_del = 0 and service_id =?";
        $this->params = array($params['channel'],$params['order_date'],$status,$user_id,$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_business_order($params,$status,$user_id){
        $this->sql = "select * from business_orders_collect where channel = ? and order_time = ? and status = ? and business_id = ? and app_id = ? and is_del = 0 and service_id =?";
        $this->params = array($params['channel'],$params['order_date'],$status,$user_id,$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_group_month_order($params,$status,$user_id){
        $this->sql = "select * from business_orders_collect where channel = ? and order_time = ? and status = ? and group_id = ? and app_id = ? and is_del = 0 and service_id =?";
        $this->params = array($params['channel'],$params['order_date'],$status,$user_id,$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_personal_day_order($params,$status,$user_id){
        $this->sql = "select * from business_orders_collect where channel = ? and order_time = ? and status = ? and user_id = ? and app_id = ? and is_del = 0 and service_id =?";
        $this->params = array($params['channel'],$params['order_date'],$status,$user_id,$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_personal_day_money($params,$collect_status,$user_id){
        $this->sql = "select * from business_money_collect where collect_time = ? and collect_status=? and user_id=? and is_del = 0 and channel=? and app_id=? and service_id=?";
        $this->params = array($params['order_date'],$collect_status,$user_id,$params['channel'],$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_group_day_money($params,$status,$user_id){
        $this->sql = "select * from business_money_collect where collect_time = ? and collect_status = ? and group_id = ? and is_del = 0 and channel=? and app_id=? and service_id=?";
        $this->params = array($params['order_date'],$status,$user_id,$params['channel'],$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_business_money($params,$status,$user_id){
        $this->sql = "select * from business_money_collect where collect_time = ? and collect_status = ? and business_id = ? and is_del = 0 and channel=? and app_id=? and service_id=?";
        $this->params = array($params['order_date'],$status,$user_id,$params['channel'],$params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function insert_orders_collect($params,$order,$user_id,$status){
        $this->sql = "insert into business_orders_collect(channel,order_time,add_time,app_id,actual_payment,payment,loss_num,sell_num,
                            user_id,group_id,`type`,status,service_id,business_id)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order['channel'],$params['order_time'],time(),$order['app_id'],$params['actual_payment'],$params['payment'],
            $params['loss_num'],$params['sell_num'],$user_id,$order['group_id'],$params['type'],$status,$order['service_id'],$order['business_id']);
        $this->doInsert();
    }

    public function insert_money_collect($params,$order,$user_id,$status){
        $this->sql = "insert into business_money_collect(app_id,wx_money,ali_money,add_time,user_id,collect_time,group_id,collect_type,collect_status,total_money,service_charge,actual_arrive,status,enter_num,enter_money,service_id,channel,business_id)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order['app_id'],$params['wx_money'],$params['ali_money'],time(),$user_id,$params['order_time'],$order['group_id'],$params['collect_type'],$status,$params['total_money'],$params['service_charge'],$params['actual_arrive'],$params['status'],$params['enter_num'],$params['enter_money'],$order['service_id'],$order['channel'],$order['business_id']);
        $this->doInsert();
    }

    public function update_orders_collect($params,$id){
        $this->sql = "update business_orders_collect set payment=?,actual_payment=?,loss_num=?,sell_num=? where id =?";
        $this->params = array($params['payment'],$params['actual_payment'],$params['loss_num'],$params['sell_num'],$id);
        $this->doExecute();
    }


    public function update_money_collect($params,$id){
        $this->sql = "update business_money_collect set wx_money=?,ali_money=?,total_money=?,service_charge=?,actual_arrive=?,enter_num=?,enter_money=? where id = ?";
        $this->params = array($params['wx_money'],$params['ali_money'],$params['total_money'],$params['service_charge'],$params['actual_arrive'],$params['enter_num'],$params['enter_money'],$id);
        $this->doExecute();
    }

    public function get_order_info($id){
        $this->sql = "select a.*,b.channel from business_group_orders a left join business_apps b on a.app_id=b.app_id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_all_order_list($params,$user_id=''){
        $this->sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from business_group_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.user_id = d.id where a.is_del=0";
        if($user_id){
            $this->sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        if($params['order_type']){
            $this->sql .= " and a.order_type = ".$params['order_type'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->sql .= " order by a.add_time desc,a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_group_list($user_id){
        $this->sql = 'select * from admins where id = ? or p1=?';
        $this->params = array($user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_personal_list($user_id){
        $this->sql = 'select * from admins where id = ? or p1=? or p2=?';
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_group_all_list(){
        $this->sql = "select * from admins where group_id = 15 and ((p1 and !p2) or (!p1 and !p2))";
        $this->doResultList();
        return $this->result;
    }

    public function get_personal_all_list(){
        $this->sql = "select * from admins where group_id = 15";
        $this->doResultList();
        return $this->result;
    }

    public function get_order_collect_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.app_name,c.real_name,c.account,c.p1,c.p2,d.service_name from business_orders_collect a left join business_apps b on a.app_id = b.app_id left join admins c on a.user_id = c.id left join business_services d on a.app_id = d.app_id and a.service_id = d.service_id where a.status = 0 and a.is_del=0";
        if($user_id){
            $this->limit_sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id = ".$params['user_id'];
        }
        if($params['group_id']){
            $this->limit_sql .= " and a.group_id = ".$params['group_id'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->limit_sql .= " and a.type = ".$params['type'];
        }else{
            $this->limit_sql .= " and a.type = 0";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
           $this->limit_sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->limit_sql .= " and a.order_time >= " .$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.order_time <= " .$params['end'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->limit_sql .= " order by a.order_time desc ,a.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_order_collect_status($id,$status){
        $this->sql = "update business_orders_collect set order_status = ? where id = ?";
        $this->params = array($status,$id);
        $this->doExecute();
    }

    public function get_order_collect_info($id){
        $this->sql = "select a.*,b.app_name,c.service_name from business_orders_collect a left join business_apps b on a.app_id=b.app_id left join business_services c on a.app_id=c.app_id and a.service_id=c.service_id where a.id = ? and a.is_del = 0";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_business_group_order($params,$id){
        $this->sql = "update business_orders_collect set actual_payment=?,other_costs=? where id =?";
        $this->params = array($params['actual_payment'],$params['other_costs'],$id);
        $this->doExecute();
    }

    public function update_business_order_collect($params){
        $this->sql = "update business_orders_collect set actual_payment=?,other_costs=?,`desc`=? where id = ?";
        $this->params = array($params['actual_payment'],$params['other_costs'],$params['desc'],$params['id']);
        $this->doExecute();
    }

    public function get_all_order_collect($params,$user_id=''){
        $this->sql = "select a.*,b.app_name,c.real_name,c.account,c.p1,c.p2,d.service_name from business_orders_collect a left join business_apps b on a.app_id = b.app_id left join admins c on a.user_id = c.id left join business_services d on a.app_id=d.app_id and a.service_id = d.service_id where a.status = 0 and a.is_del=0";
        if($user_id){
            $this->sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['user_id']){
            $this->sql .= " and a.user_id = ".$params['user_id'];
        }
        if($params['group_id']){
            $this->sql .= " and a.group_id = ".$params['group_id'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->sql .= " and a.type = ".$params['type'];
        }else{
            $this->sql .= " and a.type = 0";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start_time']){
            $this->sql .= " and a.order_time >= " .date('Ymd',strtotime($params['start_time']));
        }
        if($params['end_time']){
            $this->sql .= " and a.order_time <= " .date('Ymd',strtotime($params['end_time']));
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function update_business_money($id,$status){
        $this->sql = "update business_money_collect set status=? where id =?";
        $this->params = array($status,$id);
        $this->doExecute();
    }

    public function get_money_collect_info($id){
        $this->sql = "select a.*,b.real_name,b.account,b.p1,b.p2 from business_money_collect a left join admins b on a.user_id = b.id where a.id = ? and a.is_del= 0";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_money_collect_info($params){
        $this->sql = "update business_money_collect set wx_img=?,ali_img=?,`desc`=?,status=? where id = ?";
        $this->params = array($params['wx_img'],$params['ali_img'],$params['desc'],2,$params['id']);
        $this->doExecute();
    }

    public function get_money_collect_list($params,$page,$user_id = ''){
        $this->limit_sql = "select a.*,b.real_name,b.account,b.p1,b.p2,c.app_name,d.service_name from business_money_collect a left join admins b on a.user_id = b.id left join business_apps c on a.app_id = c.app_id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where a.collect_status = 0 and a.is_del = 0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->limit_sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->limit_sql .= " and a.collect_type = 0";
        }
        if($params['order_status']){
            if($user_id){
                $this->limit_sql .= " and ((a.status !=0 and a.status != 1 and a.user_id !=".$_SESSION['usr_id']." and a.user_id in (".$user_id.")) or a.user_id = ".$_SESSION['usr_id'].")";
            }else{
                $this->limit_sql .= " and ((a.status !=0 and a.status != 1 and a.user_id !=".$_SESSION['usr_id'].") or a.user_id = ".$_SESSION['usr_id'].")";
            }
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id =".$params['app_id'];
        }
        if($params['group_id']){
            $this->limit_sql .= " and a.group_id =".$params['group_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id =".$params['service_id'];
        }
        if($params['start']){
            $this->limit_sql .= " and a.collect_time >= ".$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.collect_time <= ".$params['end'];
        }
        if($user_id){
            $this->limit_sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id = ".$params['user_id'];
        }
        $this->limit_sql .= " order by a.add_time desc,a.collect_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_money_collect_all($params,$user_id = ''){
        $this->sql = "select a.*,b.real_name,b.account,b.p1,b.p2,c.app_name,d.service_name from business_money_collect a left join admins b on a.user_id = b.id left join business_apps c on a.app_id = c.app_id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where a.collect_status = 0 and a.is_del=0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->sql .= " and a.collect_type = 0";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel = ".$params['channel'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['order_status']){
            $this->sql .= " and a.status !=0 and a.status != 1";
        }
        if($params['start']){
            $this->sql .= " and a.collect_time >= ".$params['start'];
        }
        if($params['end']){
            $this->sql .= " and a.collect_time <= ".$params['end'];
        }
        if($user_id){
            $this->sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['user_id']){
            $this->sql .= " and a.user_id = ".$params['user_id'];
        }
        if($params['group_id']){
            $this->sql .= " and a.group_id = ".$params['group_id'];
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_group_order_collect_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.app_name,c.real_name,c.account,c.p1,c.p2,d.service_name from business_orders_collect a left join business_apps b on a.app_id = b.app_id left join admins c on a.group_id = c.id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where a.status = 1 and a.is_del = 0";
        if($user_id){
            $this->limit_sql .= " and a.group_id in (".$user_id.")";
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->limit_sql .= " and a.type = ".$params['type'];
        }else{
            $this->limit_sql .= " and a.type = 0";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->limit_sql .= " and a.order_time >= " .$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.order_time <= " .$params['end'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_business_order_collect_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.app_name,c.service_name from business_orders_collect a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id=c.app_id and a.service_id=c.service_id where a.status = 2 and a.is_del = 0";
        if($user_id){
            $this->limit_sql .= " and a.business_id in (".$user_id.")";
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->limit_sql .= " and a.type = ".$params['type'];
        }else{
            $this->limit_sql .= " and a.type = 0";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->limit_sql .= " and a.order_time >= " .$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.order_time <= " .$params['end'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_group_order_collect_all($params,$user_id=''){
        $this->sql = "select a.*,b.app_name,c.real_name,c.account,c.p1,c.p2,d.service_name from business_orders_collect a left join business_apps b on a.app_id = b.app_id left join admins c on a.group_id = c.id left join business_services d on a.app_id=d.app_id and a.service_id = d.service_id where a.status = 1 and a.is_del=0";
        if($user_id){
            $this->sql .= " and a.group_id = ".$user_id;
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->sql .= " and a.type = ".$params['type'];
        }else{
            $this->sql .= " and a.type = 0";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->sql .= " and a.order_time >= " .$params['start'];
        }
        if($params['end']){
            $this->sql .= " and a.order_time <= " .$params['end'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_group_money_collect_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.real_name,b.account,b.p1,b.p2,c.app_name,d.service_name from business_money_collect a left join admins b on a.group_id = b.id left join business_apps c on a.app_id=c.app_id left join business_services d on a.app_id =d.app_id and a.service_id=d.service_id where a.collect_status = 1 and a.is_del = 0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->limit_sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->limit_sql .= " and a.collect_type = 0";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id =".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id =".$params['service_id'];
        }
        if($params['start']){
            $this->limit_sql .= " and a.collect_time >=".$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.collect_time <=".$params['end'];
        }
        if($user_id){
            $this->limit_sql .= " and (a.user_id in (".$user_id .") or a.group_id in (".$user_id."))";
        }
        $this->limit_sql .= " order by a.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_business_money_collect_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,c.app_name,d.service_name from business_money_collect a left join business_apps c on a.app_id=c.app_id left join business_services d on a.app_id =d.app_id and a.service_id=d.service_id where a.collect_status = 2 and a.is_del = 0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->limit_sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->limit_sql .= " and a.collect_type = 0";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->limit_sql .= " and a.collect_time >=".$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.collect_time <=".$params['end'];
        }
        if($user_id){
            $this->limit_sql .= " and a.business_id in (".$user_id.")";
        }
        $this->limit_sql .= " order by a.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_group_money_collect_all($params,$user_id=''){
        $this->sql = "select a.*,b.real_name,b.account,b.p1,b.p2,c.app_name,d.service_name from business_money_collect a left join admins b on a.group_id = b.id left join business_apps c on a.app_id=c.app_id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where a.collect_status = 1 and a.is_del = 0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->sql .= " and a.collect_type = 0";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel = ".$params['channel'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['start']){
            $this->sql .= " and a.collect_time >=".$params['start'];
        }
        if($params['end']){
            $this->sql .= " and a.collect_time <=".$params['end'];
        }
        if($user_id){
            $this->sql .= " and (a.user_id = ".$user_id ." or a.group_id =".$user_id.")";
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_money_verify_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.real_name,b.account,b.p1,b.p2 from business_money_collect a left join admins b on a.user_id = b.id where a.collect_status = 0 and a.status = 2 or a.status = 4 and a.is_del = 0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->limit_sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->limit_sql .= " and a.collect_type = 0";
        }
//        if($params['order_status']){
//            $this->limit_sql .= " and a.status =3";
//        }
        if($params['start']){
            $this->limit_sql .= " and a.collect_time >= ".$params['start'];
        }
        if($params['end']){
            $this->limit_sql .= " and a.collect_time <= ".$params['end'];
        }
        if($user_id){
            $this->limit_sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id = ".$params['user_id'];
        }
        $this->limit_sql .= " order by a.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_money_info($params,$id){
        $this->sql = "update business_money_collect set enter_money=?,enter_num=? where id =?";
        $this->params = array($params['enter_money'],$params['enter_num'],$id);
        $this->doExecute();
    }

    public function update_money_status($params,$user_id){
        $this->sql = "update business_money_collect set status=?,`desc`=?,verify_time=?,operation_id=? where id =?";
        $this->params = array($params['status'],$params['desc'],time(),$user_id,$params['id']);
        $this->doExecute();
    }

    public function get_stock_info($params,$user_id){
        $this->sql = "select * from business_stock_info  where app_id =? and service_id =? and user_id =?";
        $this->params = array($params['app_id'],$params['service_id'],$user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_stock_info($params,$user_id){
        $this->sql = "update business_stock_info set stock_balance=?,stock_collect=? where app_id=? and service_id=? and user_id=?";
        $this->params = array($params['new_stock_balance'],$params['new_stock_collect'],$params['app_id'],$params['service_id'],$user_id);
        $this->doExecute();
    }

    public function insert_stock_record($params,$info,$user_id){
        $this->sql = "insert into business_stock_record (app_id,service_id,stock_num,stock_balance,new_stock_balance,stock_collect,add_time,`desc`,user_id,operator_id,p_id) values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],$params['service_id'],$params['stock_num'],$info['stock_balance'],$params['new_stock_balance'],$params['new_stock_collect'],time(),$params['desc'],$info['user_id'],$user_id,$info['id']);
        $this->doInsert();
    }

    public function get_service_name($params){
        $this->sql = "select service_name from business_services where app_id=? and service_id = ?";
        $this->params = array($params['app_id'],$params['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function insert_money_detail($params,$user_id){
        $this->sql = "insert into business_money_detail(user_id,app_id,service_id,channel,`type`,ali_money,wx_money,order_id,add_time,pay_mode,do_time,sell_time) values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($user_id,$params['app_id'],$params['service_id'],$params['channel'],$params['type'],$params['ali_money'],$params['wx_money'],$params['order_id'],time(),$params['pay_mode'],$params['do_time'],$params['sell_time']);
        $this->doInsert();
    }

    public function get_business_list(){
        $this->sql = "select * from admins where group_id = 15 and !p1 and !p2";
        $this->doResultList();
        return $this->result;
    }

    public function del_order($info,$user_id){
        $this->sql = "update business_group_orders set is_del=1,operation_id=?,del_time=? where id=?";
        $this->params = array($user_id,time(),$info['id']);
        $this->doExecute();

        $this->sql = "update business_money_detail set is_del=1 where order_id =?";
        $this->params = array($info['order_id']);
        $this->doExecute();
    }

    public function get_del_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from business_group_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.operation_id = d.id where a.is_del=1";
        if($user_id){
            $this->limit_sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        if($params['order_type']){
            $this->limit_sql .= " and a.order_type = ".$params['order_type'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->limit_sql .= " order by a.del_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_all_del_list($params,$user_id=''){
        $this->sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from business_group_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.operation_id = d.id where a.is_del=1";
        if($user_id){
            $this->sql .= " and a.user_id in (".$user_id.")";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        if($params['order_type']){
            $this->sql .= " and a.order_type = ".$params['order_type'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->sql .= " order by a.add_time desc,a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_money_detail($page,$params,$user_list=''){
        $this->limit_sql = "select a.*,b.app_name,c.real_name,c.account,c.p1,c.p2,d.service_name from business_money_detail a left join business_apps b on a.app_id=b.app_id left join admins c on a.user_id=c.id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where a.is_del = 0";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id =".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id =".$params['service_id'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode =".$params['pay_mode'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type']==='0'){
            $this->limit_sql .= " and a.type =".$params['type'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id =".$params['user_id'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id ='".trim($params['order_id'])."'";
        }
        if($user_list){
            $this->limit_sql .= " and a.user_id in (".$user_list.")";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.sell_time >=".$params['start_time'];
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.sell_time <=".$params['end_time'];
        }
        $this->limit_sql .= " order by a.sell_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_wx_money($params,$user_list=''){
        $this->sql = "select cast(sum(wx_money) as DECIMAL(14,2)) as num from business_money_detail where is_del = 0 " ;
        if($user_list){
            $this->sql .= " and user_id in (".$user_list.")";
        }
        if($params['app_id']){
            $this->sql .= " and app_id =".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->sql .= " and service_id =".$params['service_id'];
        }
        if($params['pay_mode']){
            $this->sql .= " and pay_mode =".$params['pay_mode'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type']==='0'){
            $this->sql .= " and type =".$params['type'];
        }
        if($params['user_id']){
            $this->sql .= " and user_id =".$params['user_id'];
        }
        if($params['start_time']){
            $this->sql .= " and sell_time >=".$params['start_time'];
        }
        if($params['end_time']){
            $this->sql .= " and sell_time <=".$params['end_time'];
        }
        if($params['order_id']){
            $this->sql .= " and order_id ='".trim($params['order_id'])."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_ali_money($params,$user_list=''){
        $this->sql = "select cast(sum(ali_money) as DECIMAL(14,2)) as num from business_money_detail where is_del = 0 " ;
        if($user_list){
            $this->sql .= " and user_id in (".$user_list.")";
        }
        if($params['app_id']){
            $this->sql .= " and app_id =".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->sql .= " and service_id =".$params['service_id'];
        }
        if($params['pay_mode']){
            $this->sql .= " and pay_mode =".$params['pay_mode'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type']==='0'){
            $this->sql .= " and type =".$params['type'];
        }
        if($params['user_id']){
            $this->sql .= " and user_id =".$params['user_id'];
        }
        if($params['start_time']){
            $this->sql .= " and sell_time >=".$params['start_time'];
        }
        if($params['end_time']){
            $this->sql .= " and sell_time <=".$params['end_time'];
        }
        if($params['order_id']){
            $this->sql .= " and order_id ='".trim($params['order_id'])."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_money_collect($data,$user_id){
        $this->sql = "select * from business_money_collect where user_id=? and app_id=? and service_id=? and (status=2 or status=3)";
        $this->params = array($user_id,$data['app_id'],$data['service_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_all_money_detail($params,$user_list=''){
        $this->sql = "select a.*,b.app_name,c.real_name,c.account,c.p1,c.p2,d.service_name from business_money_detail a left join business_apps b on a.app_id=b.app_id left join admins c on a.user_id=c.id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where a.is_del = 0";
        if($params['app_id']){
            $this->sql .= " and a.app_id =".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id =".$params['service_id'];
        }
        if($params['pay_mode']){
            $this->sql .= " and a.pay_mode =".$params['pay_mode'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type']==='0'){
            $this->sql .= " and a.type =".$params['type'];
        }
        if($params['user_id']){
            $this->sql .= " and a.user_id =".$params['user_id'];
        }
        if($user_list){
            $this->sql .= " and a.user_id in (".$user_list.")";
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id ='".trim($params['order_id'])."'";
        }
        if($params['start_time']){
            $this->sql .= " and a.sell_time >=".$params['start_time'];
        }
        if($params['end_time']){
            $this->sql .= " and a.sell_time <=".$params['end_time'];
        }
        $this->sql .= " order by a.sell_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function insert_game_sell($params,$user_id){
        $this->sql = "insert into business_game_sell(channel,app_id,service_id,buy_name,role_job,role_name,role_sex,vip_grade,status,login_account,`type`,login_pwd,
pay_mode,in_money,exit_depot,do_time,sell_time,operation_id,user_id,account_desc,add_time,order_id,order_status,enter_depot,re_money) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['channel'],$params['app_id'],$params['service_id'],$params['buy_name'],$params['role_job'],$params['role_name'],$params['role_sex'],
            $params['vip_grade'],$params['status'],$params['login_account'],$params['type'],$params['login_pwd'],$params['pay_mode'],$params['in_money'],$params['exit_depot'],
            $params['do_time'],$params['sell_time'],$params['operation_id'],$user_id,$params['account_desc'],time(),$params['order_id'],$params['order_status'],$params['enter_depot'],$params['re_money']);
        $this->doInsert();
    }

    public function get_sell_list($params,$page,$user_id=''){
        $this->limit_sql = "select a.*,b.account,c.app_name,d.service_name from business_game_sell a left join admins b on a.operation_id=b.id left join business_apps c on a.app_id=c.app_id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where 1=1 ";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and a.channel =".$params['channel'];
        }
        if($params['buy_name']){
            $this->limit_sql .= " and a.buy_name ='".trim($params['buy_name'])."'";
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id =".$params['service_id'];
        }
        if($params['order_status'] && is_numeric($params['order_status']) || $params['order_status'] === '0'){
            $this->limit_sql .= " and a.order_status =".$params['order_status'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode =".$params['pay_mode'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === '0'){
            $this->limit_sql .= " and a.status =".$params['status'];
        }
        if($params['type']){
            $this->limit_sql .= " and a.type =".$params['type'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id ='".$params['order_id']."'";
        }
        if($user_id){
            $this->limit_sql .= " and a.user_id =".$user_id;
        }
        $this->limit_sql .= " order by a.add_time desc,a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_sell_info($id){
        $this->sql = "select a.*,b.app_name,c.service_name from business_game_sell a left join business_apps b on a.app_id=b.app_id left join business_services c on a.app_id=c.app_id and a.service_id=c.service_id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_sell_info($params,$user_id){
        $this->sql = "update business_game_sell set buy_name=?,pay_mode=?,sell_time=?,in_money=?,operation_id=?,status=? where id=?";
        $this->params = array($params['buy_name'],$params['pay_mode'],$params['sell_time'],$params['in_money'],$user_id,1,$params['id']);
        $this->doExecute();
    }

    public function update_money_detail($params,$info,$user_id){
        $this->sql = "update business_money_detail set ali_money=?,wx_money=?,pay_mode=?,sell_time=?,user_id=? where order_id=?";
        $this->params = array($params['ali_money'],$params['wx_money'],$params['pay_mode'],$params['sell_time'],$user_id,$info['order_id']);
        $this->doExecute();
    }

    public function update_edit_money_detail($params,$order_id){
        $this->sql = 'update business_money_detail set ali_money=?,wx_money=?,pay_mode=?,app_id=?,service_id=?,channel=? where order_id=?';
        $this->params = array($params['ali_money'],$params['wx_money'],$params['pay_mode'],$params['app_id'],$params['service_id'],$params['channel'],$order_id);
        $this->doExecute();
    }

    public function get_all_sell_list($params,$user_id=''){
        $this->sql = "select a.*,b.real_name,b.account,b.p1,b.p2,c.app_name,d.service_name from business_game_sell a left join admins b on a.operation_id=b.id left join business_apps c on a.app_id=c.app_id left join business_services d on a.app_id=d.app_id and a.service_id=d.service_id where 1=1 ";
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel =".$params['channel'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id =".$params['service_id'];
        }
        if($params['buy_name']){
            $this->sql .= " and a.buy_name ='".trim($params['buy_name'])."'";
        }
        if($params['pay_mode']){
            $this->sql .= " and a.pay_mode =".$params['pay_mode'];
        }
        if($params['order_status'] && is_numeric($params['order_status']) || $params['order_status'] === '0'){
            $this->sql .= " and a.order_status =".$params['order_status'];
        }
        if($params['status'] && is_numeric($params['status']) || $params['status'] === '0'){
            $this->sql .= " and a.status =".$params['status'];
        }
        if($params['type']){
            $this->sql .= " and a.type =".$params['type'];
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id ='".$params['order_id']."'";
        }
        if($user_id){
            $this->sql .= " and a.user_id =".$user_id;
        }
        $this->sql .= " order by a.add_time desc,a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_id($user_name){
        $this->sql = "select * from admins where account=?";
        $this->params = array($user_name);
        $this->doResult();
        return $this->result;
    }

    public function get_all_business_order_list($params,$user_id=""){
        $this->sql = "select a.*,b.app_name,c.service_name,d.real_name,d.account,d.p1,d.p2 from business_orders_collect a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id=c.app_id and a.service_id=c.service_id left join admins d on a.business_id=d.id where a.status = 2 and a.is_del = 0";
        if($user_id){
            $this->sql .= " and a.business_id in (".$user_id.")";
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->sql .= " and a.type = ".$params['type'];
        }else{
            $this->sql .= " and a.type = 0";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->sql .= " and a.order_time >= " .$params['start'];
        }
        if($params['end']){
            $this->sql .= " and a.order_time <= " .$params['end'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        $this->sql .= " order by a.add_time desc,a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_all_business_money_list($params,$user_id=''){
        $this->sql = "select a.*,b.real_name,b.account,b.p1,b.p2,c.app_name,d.service_name from business_money_collect a left join admins b on a.business_id=b.id left join business_apps c on a.app_id=c.app_id left join business_services d on a.app_id =d.app_id and a.service_id=d.service_id where a.collect_status = 2 and a.is_del = 0";
        if($params['collect_type'] && is_numeric($params['collect_type']) || $params['collect_type'] === '0'){
            $this->sql .= " and a.collect_type = ".$params['collect_type'];
        }else{
            $this->sql .= " and a.collect_type = 0";
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['service_id']){
            $this->sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['channel']){
            $this->sql .= " and a.channel = ".$params['channel'];
        }
        if($params['start']){
            $this->sql .= " and a.collect_time >=".$params['start'];
        }
        if($params['end']){
            $this->sql .= " and a.collect_time <=".$params['end'];
        }
        if($user_id){
            $this->sql .= " and a.business_id in (".$user_id.")";
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_repair_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name,c.service_name,d.real_name from business_repair_log a left join business_apps b on a.app_id=b.app_id left join business_services c on a.app_id=c.app_id and a.service_id=c.service_id left join admins d on a.user_id = d.id where 1=1";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id = ".$params['user_id'];
        }
        if($params['type']){
            $this->limit_sql .= " and a.type = ".$params['type'];
        }
        if($params['user_id']){
            $this->limit_sql .= " and a.user_id = ".$params['user_id'];
        }
        $this->limit_sql .= " order by a.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_repair_log($params,$info,$user_id){
        $this->sql = "insert into business_repair_log(`type`,add_time,`desc`,stock_num,stock_balance,stock_collect,app_id,service_id,old_stock_collect,old_stock_balance,operation_id,user_id) values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['type'],time(),$params['desc'],$params['stock_num'],$params['new_stock_balance'],$params['new_stock_collect'],$params['app_id'],$params['service_id'],$info['stock_collect'],$info['stock_balance'],$user_id,$params['user_id']);
        $this->doExecute();
    }

}