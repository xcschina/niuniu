<?php
COMMON('niuniuDao');
class super_orders_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "orders";
    }

    public function get_order_list($page, $app_id,$params){
        $this->limit_sql ="select a.*,b.app_name from super_orders as a inner join super_apps as b on a.app_id=b.app_id where 1=1";
        if($app_id){
            $this->limit_sql .= " and a.app_id=".$app_id;
        }
        if($params['status']){
            $this->limit_sql .= " and a.status='".$params['status']."'";
        }
        if($params['status'] === '0'){
            $this->limit_sql .= " and a.status='".$params['status']."'";
        }
        if($params['buyer_id']){
            $this->limit_sql .= " and a.buyer_id= '".$params['buyer_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.buy_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.buy_time <=".strtotime($params['end_time']);
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['pay_order_id']){
            $this->limit_sql .= " and a.pay_order_id = '".$params['pay_order_id']."'";
        }
        if($params['channel']){
            $this->limit_sql .="  and a.channel = '".$params['channel']."'";
        }
//        if($_SESSION['group_id']<>1){
//            if(empty($_SESSION['rh_apps'])){
//                $this->limit_sql .= " and a.app_id=''";
//            }else{
//                $this->limit_sql .= " and a.app_id in (".$_SESSION['rh_apps'].")";
//            }
//        }
        if($params['qa'] == "1"){
            $this->limit_sql .= " and a.buyer_id not in('15443','71','5632','13478','13474')";
        }elseif($params['qa'] == "2"){
            $this->limit_sql .= " and a.buyer_id in('15443','71','5632','13478','13474')";
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_export_order($params){
        $this->sql ="select a.*,b.app_name from super_orders as a inner join super_apps as b on a.app_id=b.app_id where 1=1";
        if($params['app_id']){
            $this->sql .= " and a.app_id=".$params['app_id'];
        }
        if($params['status']){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['status'] === '0'){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id= '".$params['buyer_id']."'";
        }
        if($params['start_time']){
            $this->sql .= " and a.buy_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.buy_time <=".strtotime($params['end_time']);
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['pay_order_id']){
            $this->sql .= " and a.pay_order_id = '".$params['pay_order_id']."'";
        }
        if($params['channel']){
            $this->sql .="  and a.channel = '".$params['channel']."'";
        }
//        if($_SESSION['group_id']<>1){
//            if(empty($_SESSION['rh_apps'])){
//                $this->sql .= " and a.app_id=''";
//            }else{
//                $this->sql .= " and a.app_id in (".$_SESSION['rh_apps'].")";
//            }
//        }
        if($params['qa'] == "1"){
            $this->sql .= " and a.buyer_id not in('15443','71','5632','13478','13474')";
        }elseif($params['qa'] == "2"){
            $this->sql .= " and a.buyer_id in('15443','71','5632','13478','13474')";
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_sum_money($app_id,$params){
        $this->sql = "select sum(a.pay_money) as money from super_orders as a inner join super_apps as b on a.app_id=b.app_id where 1=1";
        if($app_id){
            $this->sql .= " and a.app_id=".$app_id;
        }
        if($params['status']){
            $this->sql .= " and a.status=".$params['status'];
        }
        if($params['status'] === '0'){
            $this->sql .= " and a.status='".$params['status']."'";
        }
        if($params['buyer_id']){
            $this->sql .= " and a.buyer_id= '".$params['buyer_id']."'";
        }
        if($params['start_time']){
            $this->sql .= " and a.buy_time >=".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.buy_time <=".strtotime($params['end_time']);
        }
        if($params['order_id']){
            $this->sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['pay_order_id']){
            $this->sql .= " and a.pay_order_id = '".$params['pay_order_id']."'";
        }
        if($params['channel']){
            $this->sql .= "  and a.channel = '".$params['channel']."'";
        }
//        if($_SESSION['group_id']<>1){
//            if(empty($_SESSION['rh_apps'])){
//                $this->sql .= " and a.app_id=''";
//            }else{
//                $this->sql .= " and a.app_id in (".$_SESSION['rh_apps'].")";
//            }
//        }
        if($params['qa'] == "1"){
            $this->sql .= " and a.buyer_id not in('15443','71','5632','13478','13474')";
        }elseif($params['qa'] == "2"){
            $this->sql .= " and a.buyer_id in('15443','71','5632','13478','13474')";
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_channel(){
        $this->sql = "select channel from super_orders group by channel";
        $this->doResultList();
        return $this->result;
    }

    public function get_error_order_list($params){
        $this->sql = "select a.*,b.app_name from super_orders as a inner join super_apps as b on a.app_id=b.app_id where a.status != 2";
        if($params['order_id']){
            $this->sql .= " and a.order_id = '".trim($params['order_id'])."'";
        }
        if($params['ch_order']){
            $this->sql .= " and a.ch_order = '".trim($params['ch_order'])."'";
        }
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($id){
        $this->sql = "select * from super_orders where id = ? and status != 2";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_order($id,$status){
        $this->sql = "update super_orders set status = ? where id = ?";
        $this->params = array($status,$id);
        $this->doExecute();
    }
}