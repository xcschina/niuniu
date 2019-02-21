<?php
COMMON('niuniuDao');
class kd_order_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "qb_orders";
    }
    public function get_all_list($page, $params){

        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name from  qb_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id  where 1=1 and a.channel_type=2";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.order_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.order_time <= ".strtotime($params['end_time']);
        }
        if($params['order_types']){
            $this->limit_sql .= " and a.order_types = ".$params['order_types'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['operator']){
            $this->limit_sql .= " and a.user_id = ".$params['operator'];
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;

    }

    public function get_list($page, $params){

        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from  qb_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.operation_id = d.id where 1=1 and a.channel_type=2";
//        if($user_id){
//            $this->limit_sql .= " and a.user_id in (".$user_id.")";
//        }
        $this->limit_sql .= " and a.operation_id = ".$_SESSION['usr_id'];
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.order_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.order_time <= ".strtotime($params['end_time']);
        }
        if($params['order_types']){
            $this->limit_sql .= " and a.order_types = ".$params['order_types'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['operator']){
            $this->limit_sql .= " and a.user_id = ".$params['operator'];
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;

    }
    public function get_order_list($page, $params){

        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from  qb_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.operation_id = d.id where a.status!=10 and a.status!=5 and a.channel_type=2";

//        if($user_id){
//            $this->limit_sql .= " and a.user_id in (".$user_id.")";
//        }
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id = ".$params['app_id'];
        }
        if($params['channel']){
            $this->limit_sql .= " and b.channel = ".$params['channel'];
        }
        if($params['pay_mode']){
            $this->limit_sql .= " and a.pay_mode = ".$params['pay_mode'];
        }
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
        }
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.order_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.order_time <= ".strtotime($params['end_time']);
        }
        if($params['order_types']){
            $this->limit_sql .= " and a.order_types = ".$params['order_types'];
        }
        if($params['service_id']){
            $this->limit_sql .= " and a.service_id = ".$params['service_id'];
        }
        if($params['operator']){
            $this->limit_sql .= " and a.user_id = ".$params['operator'];
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
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
    public function get_app_info($app_id){
        $this->sql = "select * from business_apps where app_id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }




    public function insert_qb_orders($params){
        $this->sql = "insert into qb_orders(channel_type,order_id,app_id,service_id,pay_mode,order_time,status,order_types,operation_id,num) values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(2,$params['order_id'],$params['app_id'],$params['service_id'],$params['pay_mode'],time(),10,$params['order_types'],$_SESSION['usr_id'],$params['num']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }


    public function get_qb_orders_info($app_id){
        $this->sql = "select * from qb_orders where id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function update_qb_order($params){
        $this->sql = "update qb_orders set money=?,status=? where id = ?";
        $this->params = array($params['money'],$params['status'],$params['id']);
        $this->doExecute();
    }
    public function update_qb_order_status($params){
        $this->sql = "update qb_orders set status=? where id = ?";
        $this->params = array($params['status'],$params['id']);
        $this->doExecute();
    }
    public function update_qb_order_status_desc($params){
        $this->sql = "update qb_orders set status=?,`refund_desc`=? where id = ?";
        $this->params = array($params['status'],$params['desc'],$params['id']);
        $this->doExecute();
    }
    public function get_user_info($user_id){
        $this->sql = "select * from admins where id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function update_qb_back_order($params){
        $this->sql = "update qb_orders set app_id=?,service_id=?,order_types=?,num=?,status=? where id = ?";
        $this->params = array($params['app_id'],$params['service_id'],$params['order_types'],$params['num'],$params['status'],$params['id']);
        $this->doExecute();
    }
    public function get_email_info($type){
        $this->sql = "select email from qb_email_config where `type`=? and is_del=2";
        $this->params = array($type);
        $this->doResultList();
        return $this->result;
    }

}