<?php
COMMON('niuniuDao');
class qb_channel_refund_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "qb_refund";
    }

    public function get_list($page, $params){
        $this->limit_sql="select a.*,b.channel_name,c.payment_account from qb_refund a left join qb_channel b on a.channel_id=b.id left join qb_channel_account c on a.account_id=c.id left join qb_orders o on a.order_id=o.order_id where 1=1 ";
        if($params['order_id']){
            $this->limit_sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['channel_id']){
            $this->limit_sql .= " and a.channel_id = '".$params['channel_id']."'";
        }
        if($params['start_time']){
            $this->limit_sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        if($params['financial_mode']){
            $this->limit_sql .= " and o.pay_mode = ".$params['financial_mode'];
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;

    }

    public function get_channel(){
        $this->sql = "select id,channel_name from qb_channel";
        $this->doResultList();
        return $this->result;
    }

    public function get_all_data($params){
        $this->sql = "select a.*,b.channel_name,c.payment_account from qb_refund a left join qb_channel b on a.channel_id=b.id left join qb_channel_account c on a.account_id=c.id where 1=1 ";

        if($params['order_id']){
            $this->sql .= " and a.order_id = '".$params['order_id']."'";
        }
        if($params['channel_id']){
            $this->sql .= " and a.channel_id = '".$params['channel_id']."'";
        }
        if($params['start_time']){
            $this->sql .= " and a.add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and a.add_time <= ".strtotime($params['end_time']);
        }
        $this->sql .= " order by a.add_time desc,a.id desc";
        $this->doResultList();
        return $this->result;
    }
    public function get_channel_name($id){
        $this->sql = "select channel_name from qb_channel where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function get_account_name($id){
        $this->sql = "select payment_account from qb_channel_account where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function update_qb_refund($params){
        $this->sql = "update qb_refund set user_id=?,operation_time=?,img1=?,img2=?,img3=?,status=? where id = ?";
        $this->params = array($_SESSION['usr_id'],time(),$params['img1'],$params['img2'],$params['img3'],$params['status'],$params['id']);
        $this->doExecute();
    }
    public function get_refund_info($id){
        $this->sql = "select * from qb_refund where id=? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function update_qb_channel_distribution($order_id,$channel_id){
        $this->sql = "update qb_channel_distribution set is_del=? where order_id=? and channel_id =?";
        $this->params = array(1,$order_id,$channel_id);
        $this->doExecute();
    }
    public function get_channel_allocated_money($order_id,$channel_id){
        $this->sql = "select sum(can_distribution_money)money from qb_channel_distribution where order_id=? and channel_id=? and is_del=1 and img_url is not null ";
        $this->params = array($order_id,$channel_id);
        $this->doResult();
        return $this->result;
    }
    public function get_channel_allocated_list($order_id,$channel_id){
        $this->sql = "select * from qb_channel_distribution where order_id=? and channel_id=? and is_del=1 and img_url is not null order by num desc";
        $this->params = array($order_id,$channel_id);
        $this->doResultList();
        return $this->result;
    }
    public function update_qb_channel_can_distribution_money($money,$id){
        $this->sql = "update qb_channel_distribution set can_distribution_money=? where id =? and img_url is not null ";
        $this->params = array($money,$id);
        $this->doExecute();
    }
    public function update_qb_channel_refund_money($money,$id){
        $this->sql = "update qb_channel_distribution set refund_money=? where id =? and img_url is not null ";
        $this->params = array($money,$id);
        $this->doExecute();
    }
    public function get_qb_channel_refund_money($id){
        $this->sql = "select refund_money from qb_channel_distribution  where id =? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function update_qb_refund_refuse_desc($params){
        $this->sql = "update qb_refund set user_id=?,operation_time=?,refuse_desc=?,status=? where id = ?";
        $this->params = array($_SESSION['usr_id'],time(),$params['refuse_desc'],2,$params['id']);
        $this->doExecute();
    }


    public function get_qb_refund_info($id){
        $this->sql = "select * from qb_refund where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function update_qb_channel_distribution_is_del($id){
        $this->sql = "update qb_channel_distribution set is_del=? where id = ? and img_url is not null ";
        $this->params = array(2,$id);
        $this->doExecute();
    }
    public function get_user_info($user_id){
        $this->sql = "select * from admins where id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function get_refund_money($order_id,$channel_id){
        $this->sql = "select sum(refund_money)money from qb_refund where order_id=? and channel_id=? and status=1";
        $this->params = array($order_id,$channel_id);
        $this->doResult();
        return $this->result;
    }

    public function get_already_allocated_money($order_id){
        $this->sql = "select sum(can_distribution_money)money from qb_channel_distribution where order_id=?  and is_del=1 and img_url is not null";
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
    public function update_qb_order_status($order_id){
        $this->sql = "update qb_orders set status=? where order_id = ?";
        $this->params = array(4,$order_id);
        $this->doExecute();
    }



}