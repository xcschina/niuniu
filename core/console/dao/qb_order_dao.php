<?php
COMMON('dao');
class qb_order_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_order($params,$page){
        $this->limit_sql="select ord.*,a.real_name from qb_order as ord left join admins as a on ord.service_id=a.id where 1=1  ";
        if($params['order_id']){
            $this->limit_sql=$this->limit_sql." and ord.order_id = '".$params['order_id']."'";
        }
        if($params['status'] || $params['status']=='0'){
            $this->limit_sql=$this->limit_sql." and ord.status = '".$params['status']."'";
        }
        if($params['buy_time'] && $params['buy_time2']){
            $this->limit_sql=$this->limit_sql .=  " and ord.buy_time>=".$params['buy_time']." and ord.buy_time<=".$params['buy_time2'];
        }else if($params['time'] && !$params['time2']) {
            $this->limit_sql=$this->limit_sql.=  " and ord.buy_time>=".$params['buy_time'];
        } else if(!$params['time'] && $params['time2']) {
            $this->limit_sql=$this->limit_sql.=  " and ord.buy_time<=".$params['buy_time2'];
        }
        if($params['reg_channel']){
            $this->limit_sql=$this->limit_sql." and reg_channel ='".$params['reg_channel']."'";
        }
        $this->limit_sql=$this->limit_sql." order by ord.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function order_details($id){
        $this->sql="select ord.*,a.real_name from qb_order as ord left join admins as a on ord.service_id=a.id where ord.id = ".$id;
        $this->doResult();
        return $this->result;
    }

    public function update_order($params){
        $this->sql="update qb_order set qb_order_id=?,remarks=?,`status`=? where id=?";
        $this->params=array($params['qb_order'],$params['remarks'],$params['order_status'],$params['id']);
        $this->doExecute();
    }

    public function get_qb_order($order_id){
        $this->sql="select * from kamen where order_id = ?";
        $this->params=array($order_id);
        $this->doResult();
        return $this->result;
    }

}