<?php
COMMON('niuniuDao');
class qb_channel_distribution_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "qb_channel_distribution";
    }

    public function get_list($page, $params){

        $this->limit_sql = "select a.*,b.app_name,b.channel,c.service_name,d.real_name,d.account,d.p1,d.p2 from  qb_orders a left join business_apps b on a.app_id = b.app_id left join business_services c on a.app_id = c.app_id and a.service_id = c.service_id left join admins d on a.operation_id = d.id where (a.status=4 or a.status=11) and a.channel_type=1";
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
        if($params['financial_mode']){
            $this->limit_sql .= " and a.pay_mode = ".$params['financial_mode'];
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
        if($params['status']){
            $this->limit_sql .= " and a.status = ".$params['status'];
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



    public function get_qb_orders_info($app_id){
        $this->sql = "select * from qb_orders where id = ?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from admins where id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_account_list($type){
        $this->sql = "select a.*,b.* from qb_channel a left join qb_channel_account b on a.id = b.channel_id where b.type=1 and b.mode=? and b.is_del=0";
        $this->params = array($type);
        $this->doResultList();
        return $this->result;
    }
    public function insert_qb_channel_distribution($params){
        $this->sql = "insert into qb_channel_distribution(add_time,user_id,can_distribution_money,status,num,payment_account,channel_id,distribution_money,user_name,order_id) values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array(time(),$_SESSION['usr_id'],$params['can_distribution_money'],$params['status'],$params['num'],$params['payment_account'],$params['channel_id'],$params['distribution_money'],$params['user_name'],$params['order_id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
    public function get_qb_channel_distribution_list($order_id){
        $this->sql = "select a.*,b.channel_name,d.credit_money from qb_channel_distribution a left join qb_channel b on a.channel_id=b.id left join qb_channel_account d on a.payment_account=d.payment_account where a.order_id=?  group by a.id ";
        $this->params = array($order_id);
        $this->doResultList();
        return $this->result;
    }
    public function get_max_num($order_id){
        $this->sql = "select max(num)num from qb_channel_distribution where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }
    public function get_allocated_money($order_id){
        $this->sql = "select sum(distribution_money)money from qb_channel_distribution where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }
    public function get_money_from_order($order_id){
        $this->sql = "select money from qb_orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }
    public function get_refund_money($order_id){
        $this->sql = "select sum(refund_money)money from qb_refund where order_id=? and status=1";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }
    public function get_credit_channel_data($order_id){
        $this->sql = "select a.*,b.channel_name,b.credit_money from qb_channel_distribution a left join qb_channel b on a.channel_id=b.id where a.order_id=? and b.type=1";
        $this->params = array($order_id);
        $this->doResultList();
        return $this->result;
    }
    public function get_credit_channel(){
        $this->sql = "select a.*,b.channel_name from qb_channel_account a left join qb_channel b on a.channel_id=b.id where mode=1 and a.is_del=0 and a.type=1";
        $this->doResultList();
        return $this->result;
    }
    public function get_allocated_credit_money($order_id,$channel_id){
        $this->sql = "select sum(can_distribution_money)money from qb_channel_distribution where order_id=? and channel_id=? and is_del=1";
        $this->params = array($order_id,$channel_id);
        $this->doResult();
        return $this->result;
    }
    public function get_channel_credit_money($channel_id){
        $this->sql = "select sum(can_distribution_money)money from qb_channel_distribution where channel_id=? and is_del=1";
        $this->params = array($channel_id);
        $this->doResult();
        return $this->result;
    }
    public function get_allocated_refund_money($order_id,$channel_id){
        $this->sql = "select sum(refund_money)money from qb_channel_distribution  where order_id=? and channel_id=?";
        $this->params = array($order_id,$channel_id);
        $this->doResult();
        return $this->result;
    }
    public function update_qb_order_distribution($order_id){
        $this->sql = "update qb_orders set distribution=? where order_id = ?";
        $this->params = array(1,$order_id);
        $this->doExecute();
    }
    public function update_qb_order_status($status,$order_id){
        $this->sql = "update qb_orders set status=? where order_id = ?";
        $this->params = array($status,$order_id);
        $this->doExecute();
    }
    public function update_qb_channel_distribution($params){
        $this->sql = "update qb_channel_distribution set img_url=? where id = ?";
        $this->params = array($params['img_url'],$params['id']);
        $this->doExecute();
    }
    public function update_qb_channel_distribution_status($params){
        $this->sql = "update qb_channel_distribution set status=? where id = ?";
        $this->params = array($params['status'],$params['id']);
        $this->doExecute();
    }
    public function get_qb_channel_distribution_status($params){
        $this->sql = "select order_id,status from qb_channel_distribution  where id = ?";
        $this->params = array($params['id']);
        $this->doResult();
        return $this->result;
    }

    public function get_qb_channel_list(){
        $this->sql = "select * from qb_channel";
        $this->doResultList();
        return $this->result;
    }
    public function get_financial_list($mode){
        $this->sql = "select payment_account,id from qb_channel_account where `type`=2 and mode=? and is_del=0";
        $this->params = array($mode);
        $this->doResultList();
        return $this->result;
    }
    public function get_channel_allocated_money($order_id,$channel_id){
        $this->sql = "select sum(can_distribution_money)money from qb_channel_distribution where order_id=? and channel_id=? and is_del=1 and img_url is not null";
        $this->params = array($order_id,$channel_id);
        $this->doResult();
        return $this->result;
    }
    public function get_channel_allocated_list($order_id,$channel_id){
        $this->sql = "select * from qb_channel_distribution where order_id=? and channel_id=? order by num desc";
        $this->params = array($order_id,$channel_id);
        $this->doResultList();
        return $this->result;
    }
    public function update_qb_channel_distribution_is_del($id){
        $this->sql = "update qb_channel_distribution set is_del=? where id = ?";
        $this->params = array(2,$id);
        $this->doExecute();
    }
    public function insert_qb_refund($params){
        $this->sql = "insert into qb_refund(order_id,account_id,channel_id,refund_money,`desc`,add_time) values(?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$params['account_id'],$params['channel_id'],$params['money'],$params['desc'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
    public function update_qb_channel_distribution_money($money,$id){
        $this->sql = "update qb_channel_distribution set distribution_money=? where id = ?";
        $this->params = array($money,$id);
        $this->doExecute();
    }
    public function get_already_allocated_money($order_id){
        $this->sql = "select sum(can_distribution_money)money from qb_channel_distribution where order_id=?  and is_del=1 and img_url is not null";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_email_info($type,$financial_type){
        $this->sql = "select email from qb_email_config where `type`=? and is_del=2 and financial_type=?";
        $this->params = array($type,$financial_type);
        $this->doResultList();
        return $this->result;
    }

}