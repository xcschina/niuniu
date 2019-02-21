<?php
COMMON('niuniuDao','randomUtils');
class business_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_order_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name,c.real_name,d.service_name from business_orders a left join business_apps b on a.app_id = b.app_id left join admins c on a.guild_id = c.id left join business_services d on a.app_id = d.app_id and a.service_id = d.service_id where 1=1";
        if($_SESSION['group_id'] == 2 || $_SESSION['group_id'] == 3 ){
            $this->limit_sql .= " and a.acc_id = ".$_SESSION['usr_id'];
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->limit_sql .= " and a.type = ".$params['type'];
        }
        if($params['guild_id']){
            $this->limit_sql .= " and a.guild_id = ".$params['guild_id'];
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
        if($params['user_id']){
            $this->limit_sql .= " and a.guild_id = ".$params['user_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        $this->limit_sql .= " order by a.add_time desc , a.update_time desc, a.id desc ";
        $this->doLimitResultList($page);
        return  $this->result;
    }

    public function get_all_order_list($params){
        $this->sql = "select a.*,b.app_name,c.real_name,d.service_name from business_orders a left join business_apps b on a.app_id = b.app_id left join admins c on a.guild_id = c.id left join business_services d on a.app_id = d.app_id and a.service_id = d.service_id where 1=1";
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['type'] && is_numeric($params['type']) || $params['type'] === '0'){
            $this->sql .= " and a.type = ".$params['type'];
        }
        if($params['guild_id']){
            $this->sql .= " and a.guild_id = ".$params['guild_id'];
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
        if($params['user_id']){
            $this->sql .= " and a.guild_id = ".$params['user_id'];
        }
        if($params['role_account']){
            $this->sql .= " and a.role_account = '".$params['role_account']."'";
        }
        if($params['status']){
            $this->sql .= " and a.status = ".$params['status'];
        }
        $this->sql .= " order by a.add_time desc ";
        $this->doResultList();
        return  $this->result;
    }

    public function get_app_list(){
        $this->sql = "select * from business_apps where is_del = 0 and chamber_type=0 or chamber_type =2 order by add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function insert_order($params,$user_info){
        $this->sql = "insert into business_orders(order_id,guild_id,app_id,service_id,role_name,role_sex,role_job,pay_money,pay_mode,img,spare_role,spare_account,`desc`,add_time,money,payment_method) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$user_info['id'],$params['app_id'],$params['service_id'],$params['role_name'],$params['role_sex'],$params['role_job'],$params['pay_money'],$params['pay_mode'],$params['img'],$params['spare_role'],$params['spare_account'],$params['desc'],time(),$params['money'],$params['payment_method']);
        $this->doInsert();
        $order_id = $this->LAST_INSERT_ID;

        if($params['pay_mode'] == '1'){
            $this->sql = "update admins set money = ? where id=?";
            $this->params = array($user_info['money']-$params['pay_money'],$user_info['id']);
            $this->doExecute();
        }
        return $order_id;
    }

    public function get_all_guild_list(){
        $this->sql = "select * from admins where group_id=14 AND is_del=0 ";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_order_info($id){
        $this->sql = "select a.*,b.app_name,b.type as app_type,b.discount,c.real_name,c.money as remian_money,d.service_name from business_orders a left join business_apps b on a.app_id = b.app_id left join admins c on a.guild_id = c.id left join business_services d on a.service_id = d.service_id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select a.*,b.mobile,b.payment_method,b.is_online,b.app_list,b.status from admins a left join admins_relation_tb b on a.id = b.user_id where a.id =?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_order($params,$id){
        $this->sql = "update business_orders set app_id=?,service_id=?,role_name=?,role_sex=?,role_job=?,img=?,spare_role=?,spare_account=?,`desc`=?,update_time=?,payment_method=? where id=?";
        $this->params = array($params['app_id'],$params['service_id'],$params['role_name'],$params['role_sex'],$params['role_job'],$params['img'],$params['spare_role'],$params['spare_account'],$params['desc'],time(),$params['payment_method'],$id);
        $this->doExecute();
    }

    public function update_order_feedback($params,$id){
        $this->sql = "update business_orders set feedback_time=?,feedback_desc=?,`type`=?,role_account=?,role_pwd=?,pay_type=?,payment_method=?,order_status=? where id=?";
        $this->params = array(time(),$params['feedback_desc'],$params['type'],$params['role_account'],$params['role_pwd'],$params['pay_type'],$params['payment_method'],$params['order_status'],$id);
        $this->doExecute();
    }


    public function get_recharge_list($page,$params,$ids=""){
        $this->limit_sql="select * from business_record_tb where 1=1 ";
        if(!empty($ids)){
            $this->limit_sql.=" and user_id in (".$ids.")";
        }
        if(!empty($params['start_time'])){
            $this->limit_sql = $this->limit_sql. " and update_time >=".strtotime($params['start_time']);
        }
        if(!empty($params['end_time'])){
            $this->limit_sql = $this->limit_sql. " and update_time <=".strtotime($params['end_time']);
        }
        if(!empty($params['status']) || is_numeric($params['status'])){
            $this->limit_sql = $this->limit_sql. " and status =".$params['status'];
        }
        $this->limit_sql.=" order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_recharge_record($id){
        $this->sql="select * from business_record_tb where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_recharge_record($data,$operator_id,$id){
        $this->sql = "update business_record_tb set status=?,reason=?,operator_id=?,update_time=? where id=?";
        $this->params = array($data['status'],$data['reason'],$operator_id,time(),$id);
        $this->doExecute();
    }

    public function guild_lock($id,$num){
        $this->sql = "update admins set money_lock=?,money_last_time=? where id=?";
        $this->params = array($num,time(),$id);
        $this->doExecute();
    }

    public function update_business_order($acc_id,$order_id){
        $this->sql = "update business_orders set acc_id = ?,order_status=? where id=?";
        $this->params = array($acc_id,'1',$order_id);
        $this->doExecute();
    }

    public function update_relation_tb($status,$user_id){
        $this->sql = "update admins_relation_tb set status = ? where user_id=?";
        $this->params = array($status,$user_id);
        $this->doExecute();
    }

    public function get_orders($app_list=''){
        $this->sql = "select a.*,b.real_name,c.app_name from business_orders a left join admins b on a.guild_id = b.id left join business_apps c on a.app_id = c.app_id where a.order_status = 0";
        if($app_list){
            $this->sql .= " and a.app_id in (".$app_list.")";
        }
        $this->doResultList();
        return $this->result;
    }

    public function update_guild_money($money,$id){
        $this->sql = "update admins set `money`=?,money_last_time=? where id=?";
        $this->params = array($money,time(),$id);
        $this->doExecute();
    }

    public function get_service_list($app_id){
        $this->sql = 'select * from business_services where app_id =?';
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_order_log($order_id,$params,$user_id){
        $this->sql = "insert into business_orders_log(order_id,add_time,`desc`,money,operation_id,app_id,service_id,role_job,role_sex,role_name,account,pwd,status,finish_time,pay_type,qb_discount,pay_money)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order_id,time(),$params['feedback_desc'],$params['money'],$user_id,$params['app_id'],$params['service_id'],$params['role_job'],$params['role_sex'],$params['role_name'],$params['role_account'],$params['role_pwd'],2,time(),$params['pay_type'],$params['discount'],$params['pay_money']);
        $this->doInsert();
    }

    public function get_order_log_money($order_id){
        $this->sql = "select cast(sum(money) as DECIMAL(14,2)) as money from business_orders_log where order_id = ?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_log_list($order_id){
        $this->sql = "select a.*,b.real_name from business_orders_log a left join admins b on a.operation_id = b.id where a.order_id = ? order by a.add_time desc";
        $this->params = array($order_id);
        $this->doResultList();
        return $this->result;
    }

    public function update_admins($info){
        $this->sql = "update admins set money = ? where id=?";
        $this->params = array($info['pay_money']+$info['remian_money'],$info['guild_id']);
        $this->doExecute();
    }

    public function get_pay_log_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name,c.service_name,d.guild_id,d.payment_method from business_orders_log a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join business_orders d on a.order_id = d.id where a.is_del = 0 and a.status != 1";
        if(!$params['pay_type'] || $params['pay_type'] == 1){
            $this->limit_sql .= " and a.pay_type = 1";
        }else{
            $this->limit_sql .= " and a.pay_type = ".$params['pay_type'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        if($params['payment']){
            $this->limit_sql .= " and d.payment_method = ".$params['payment'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_log_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name,c.service_name,d.guild_id,e.real_name from business_orders_log a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join business_orders d on a.order_id = d.id left join admins e on a.operation_id = e.id where a.is_del = 0 and a.status != 10";
//        if(!$params['pay_type'] || $params['pay_type'] == 1){
//            $this->limit_sql .= " and a.pay_type = 1";
//        }else{
//            $this->limit_sql .= " and a.pay_type in (2,3,4,5,6)";
//        }
        if($params['pay_type']){
            if($params['pay_type'] == 1){
                $this->limit_sql .= " and a.pay_type = 1";
            }else{
                $this->limit_sql .= " and a.pay_type = ".$params['pay_type'];
            }
        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['guild_id']){
            $this->limit_sql .= " and d.guild_id = ".$params['guild_id'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        if($params['account']){
            $this->limit_sql .= " and a.account = ".$params['account'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by a.add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_log($params,$user_id){
        $this->sql = "insert into business_orders_log(app_id,add_time,`desc`,money,operation_id,service_id,role_name,role_sex,role_job,account,pwd,pay_type,pay_money,qb_discount)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['app_id'],time(),$params['desc'],$params['money'],$user_id,$params['service_id'],$params['role_name'],$params['role_sex'],$params['role_job'],$params['account'],$params['pwd'],$params['pay_type'],$params['pay_money'],$params['discount']);
        $this->doInsert();

        $log_id = $this->LAST_INSERT_ID;

        $this->sql = "insert into business_account_charge_log(log_id,money,charge_time,operation_id,message)values(?,?,?,?,?)";
        $this->params = array($log_id,$params['money'],time(),$user_id,$params['desc']);
        $this->doInsert();
    }

    public function get_log_info($id){
        $this->sql = "select a.*,b.app_name,c.service_name,d.real_name from business_orders_log a left join business_apps b on a.app_id = b.app_id left join business_services c on a.service_id = c.service_id left join admins d on a.operation_id = d.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_log($params,$user_id){
        $this->sql = "update business_orders_log set `desc`=?,money=?,pay_money=? where id = ?";
        $this->params = array($params['desc'],$params['money'],$params['pay_money'],$params['id']);
        $this->doExecute();

        $this->sql = "insert into business_account_charge_log (log_id,money,charge_time,operation_id,message)values(?,?,?,?,?)";
        $this->params = array($params['id'],$params['pay_money'],time(),$user_id,$params['desc']);
        $this->doInsert();
    }

    public function get_account_list($info){
        $this->sql = "select * from business_orders_log where status=1 and app_id =? and service_id =?";
        $this->params = array($info['app_id'],$info['service_id']);
        $this->doResultList();
        return $this->result;
    }

    public function get_account_info($id){
        $this->sql = "select * from business_orders_log where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_order_log($id,$order_id){
        $this->sql = "update business_orders_log set finish_time = ?,status=?,order_id=? where id=?";
        $this->params = array(time(),2,$order_id,$id);
        $this->doExecute();
    }

    public function get_my_account_list($info){
        $this->sql = "select * from business_orders_log where service_id = ? and app_id =? and account = ?";
        $this->params = array($info['service_id'],$info['app_id'],$info['role_account']);
        $this->doResultList();
        return $this->result;
    }

    public function update_order_type($params,$order_id){
        $this->sql = "update business_orders set `type` = ?,order_status = ? where id =?";
        $this->params = array($params['type'],$params['order_status'],$order_id);
        $this->doExecute();
    }

    public function get_app_info($app_id){
        $this->sql = "select * from business_apps where app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_qq($app_id=''){
        $this->sql = "select a.qq,a.id,b.app_list,b.status from admins a left join admins_relation_tb b on a.id=b.user_id where b.is_online=1 and b.status<3";
        if($app_id){
            $this->sql .= " and find_in_set('".$app_id."', b.app_list)";
        }
        $this->doResultList();
        return $this->result;
    }

    public function insert_payment($params,$info){
        $this->sql = "insert into business_orders_log(app_id,add_time,money,operation_id,service_id,role_name,role_sex,role_job,pay_type,account,status,pay_money,finish_time)values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(99999,strtotime($params['payment_time']),$params['money'],$info['id'],88888,$info['real_name'],0,'客服',$params['pay_type'],$info['account'],10,$params['money'],time());
        $this->doInsert();
    }

    public function get_payment_num($params){
        $this->sql = "select cast(sum(a.pay_money) as DECIMAL(14,2)) as num from business_orders_log as a left join business_orders d on a.order_id = d.id  where a.status = 10 and a.is_del = 0";
        if(!$params['pay_type']){
            $this->sql .= " and a.pay_type = 1";
        }else{
            $this->sql .= " and a.pay_type = ".$params['pay_type'];
        }
        if($params['payment']){
            $this->sql .= " and d.payment_method = ".$params['payment'];
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->doResult();
        return $this->result;
    }

    public function get_money_num($params){
        $this->sql = "select cast(sum(a.pay_money) as DECIMAL(14,2)) as num from business_orders_log a left join business_apps b on a.app_id = b.app_id left join business_services c on a.service_id = c.service_id left join business_orders d on a.order_id = d.id where a.status !=10";
//        if(!$params['pay_type'] || $params['pay_type'] == 1){
//            $this->sql .= " and a.pay_type = 1";
//        }else{
//            $this->sql .= " and a.pay_type != 1";
//        }
        if($params['pay_type']){
            if($params['pay_type'] == 1){
                $this->limit_sql .= " and a.pay_type = 1";
            }else{
                $this->limit_sql .= " and a.pay_type = ".$params['pay_type'];
            }
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['guild_id']){
            $this->sql .= " and d.guild_id = ".$params['guild_id'];
        }
        if($params['status']){
            $this->sql .= " and a.status = ".$params['status'];
        }
        if($params['account']){
            $this->sql .= " and a.account = ".$params['account'];
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->doResult();
        return $this->result;
    }

    public function get_pay_money_num($params){
        $this->sql = "select cast(sum(a.pay_money) as DECIMAL(14,2)) as num from business_orders_log a left join business_orders d on a.order_id = d.id  where a.status = 2 and a.is_del = 0";
        if(!$params['pay_type'] || $params['pay_type'] == '1'){
            $this->sql .= " and a.pay_type = 1";
        }else{
            $this->sql .= " and a.pay_type = ".$params['pay_type'];
        }
        if($params['payment']){
            $this->sql .= " and d.payment_method = ".$params['payment'];
        }
        if($params['status']){
            $this->sql .= " and a.status = ".$params['status'];
        }
//        if($params['pay_type']){
//            $this->sql .= " and pay_type = ".$params['pay_type'];
//            $this->sql .= " and status = 2";
//        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->doResult();
        return $this->result;
    }

    public function import_orders($import_data,$user_info){
        $this->doAffairsBegin();
        $sql = 'INSERT INTO business_orders(order_id,guild_id,app_id,service_id,role_name,role_sex,role_job,pay_money,pay_mode,img,spare_role,spare_account,`desc`,add_time,money)VALUES';
        foreach ($import_data as $value){
            $sql .= "('".$value['order_id']."',".$value['guild_id'].",".$value['app_id'].",".$value['service_id'].",'".$value['role_name']."',
                    ".$value['role_sex'].",'".$value['role_job']."',".$value['pay_money'].",".$value['pay_mode'].",'".$value['img']."',
                    '".$value['spare_role']."','".$value['spare_account']."','".$value['desc']."',".time().",".$value['money']."),";
        }
        $this->sql = trim($sql,',');
        $this->doInsert();
        if ($this->LAST_INSERT_ID){
            if($user_info['update_money'] !== 0){
                $this->sql = "update admins set money = ? where id=?";
                $this->params = array($user_info['update_money'],$user_info['id']);
                $this->doExecute();
                if (!$this->rows) {
                    $this->doAffairsRollback();
                    return false;
                }
            }
            //插入记录日志
            $sql = 'INSERT INTO business_money_log(order_id,`type`,money,add_time,operation_id,pay_type,pay_money)VALUES';
            foreach ($import_data as $value_log){
                $sql .= "('".$value_log['order_id']."',2,".$value_log['money'].",".time().",".$user_info['id'].",".$value_log['pay_mode'].",".$value_log['pay_money']."),";
            }
            $this->sql = trim($sql,',');
            $this->doInsert();
            if ($this->LAST_INSERT_ID){
                $this->doAffairsCommit();
            }else{
                $this->doAffairsRollback();
                return false;
            }
        }else{
            $this->doAffairsRollback();
            return false;
        }
        return true;
    }

    public function get_game_services_all($data){
//        $this->sql = "SELECT s.app_id,a.app_name,s.service_id,s.id,s.service_name,a.discount FROM business_services s LEFT JOIN business_apps a ON s.app_id=a.app_id WHERE s.is_del=0 AND a.is_del=0 and a.app_id in("..") ORDER BY s.id";
        $this->sql = "SELECT s.app_id,a.app_name,s.service_id,s.id,s.service_name,a.discount FROM business_services s LEFT JOIN business_apps a ON s.app_id=a.app_id WHERE s.is_del=0 AND a.is_del=0 and a.app_id in(".$data['game_list'].") and s.id in (".$data['service_list'].") ORDER BY s.id";
        $this->doResultList();
        return $this->result;
    }

    public function check_account($params){
        $this->sql = "select * from business_orders where app_id =? and service_id =? and role_account = ?";
        $this->params = array($params['app_id'],$params['service_id'],$params['role_account']);
        $this->doResult();
        return $this->result;
    }

    public function insert_operation_log($reason,$user_id){
        $guid = randomUtils::guid();
        $this->sql="insert into `66173`.user_operation_log(guid,user_id,op_type,op_results,op_desc,op_time) values (?,?,?,?,?,?)";
        $this->params=array($guid,$user_id,7,1,$reason,strtotime("now"));
        $this->doInsert();
    }

    public function del_order($id){
        $this->sql = "update business_orders_log set is_del = 1 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function insert_money_log($params,$user_id){
        $this->sql = "insert into business_money_log(order_id,`type`,money,add_time,operation_id,pay_type,pay_money) values(?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$params['money_type'],$params['money'],time(),$user_id,$params['pay_mode'],$params['pay_money']);
        $this->doInsert();
    }

    public function get_all_detail_list($params){
        $this->sql = "select a.*,b.app_name,c.service_name,d.guild_id from business_orders_log a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join business_orders d on a.order_id = d.id where a.is_del = 0 and a.status != 1";
        if(!$params['pay_type'] || $params['pay_type'] == 1){
            $this->sql .= " and a.pay_type = 1";
        }else{
            $this->sql .= " and a.pay_type = ".$params['pay_type'];
        }
        if($params['status']){
            $this->sql .= " and a.status = ".$params['status'];
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->sql .= " order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_order_log_info($id){
        $this->sql = "select * from business_orders_log where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_remain_money($params){
        $this->sql = "select remain_money from business_orders_log where is_del = 0 and remain_money  is not null and status != 1";
        if($params['pay_type']){
            $this->sql .= " and pay_type = ".$params['pay_type'];
        }
        $this->sql .= " order by id desc";
        $this->doResult();
        return $this->result;
    }

    public function insert_refill_order($params,$user_info){
        $this->sql = "insert into business_orders(order_id,guild_id,app_id,service_id,role_name,role_sex,role_job,pay_money,pay_mode,img,role_account,role_pwd,`desc`,add_time,money,status) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$user_info['id'],$params['app_id'],$params['service_id'],$params['role_name'],$params['role_sex'],$params['role_job'],$params['pay_money'],$params['pay_mode'],$params['img'],$params['role_account'],$params['role_pwd'],$params['desc'],time(),$params['money'],1);
        $this->doInsert();
        $order_id = $this->LAST_INSERT_ID;
        if($params['pay_mode'] == '1'){
            $this->sql = "update admins set money = ? where id=?";
            $this->params = array($user_info['money']-$params['pay_money'],$user_info['id']);
            $this->doExecute();
        }
        return $order_id;
    }

    public function get_business_app_list($user_id){
        $this->sql = " select * from business_game_dis where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_business_service_list($app_id,$service_list){
        $this->sql = "select * from business_services where app_id = ".$app_id." and id in (".$service_list.")";
        $this->doResultList();
        return $this->result;
    }

    public function get_relation_info($user_id){
        $this->sql = "select * from admins_relation_tb where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_relation_info($user_id,$params){
        $this->sql = "update admins_relation_tb set is_online = ?,app_list=? where user_id =?" ;
        $this->params = array($params['is_online'],$params['app_list'],$user_id);
        $this->doExecute();
    }

    public function insert_relation_tb($user_id,$params){
        $this->sql = "insert into admins_relation_tb (user_id,add_time,is_online,app_list) values (?,?,?,?)";
        $this->params = array($user_id,time(),$params['is_online'],$params['app_list']);
        $this->doInsert();
    }

    public function get_log_list_all($params){
        $this->sql = "select a.*,b.app_name,c.service_name,d.guild_id,e.real_name from business_orders_log a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join business_orders d on a.order_id = d.id left join admins e on a.operation_id = e.id where a.is_del = 0 and a.status != 10";
        if($params['pay_type']){
            if($params['pay_type'] == 1){
                $this->sql .= " and a.pay_type = 1";
            }else{
                $this->sql .= " and a.pay_type = ".$params['pay_type'];
            }
        }
        if($params['app_id']){
            $this->sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['guild_id']){
            $this->sql .= " and d.guild_id = ".$params['guild_id'];
        }
        if($params['status']){
            $this->sql .= " and a.status = ".$params['status'];
        }
        if($params['account']){
            $this->sql .= " and a.account = ".$params['account'];
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }
}