<?php
COMMON('dao');

class qb_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_qq_rate($id) {
        $this->sql = "select * from setting where id = ".$id;
        $this->doResult();
        return $this->result;
    }

    public function get_service(){
        $this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 AND last_service_time>0 and id <>'114' order by last_service_time limit 1";
        $this->doResult();
        return $this->result;
    }

    public function insert_order($order){
        $this->sql = "insert into qb_order(title,buyer_id,order_id,amount,unit_price,money,pay_money,pay_channel,buy_remark,tel,charge_qq,service_id,buy_time)
                                    values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($order['title'],$order['buyer_id'],$order['order_id'],$order['amount'],$order['unit_price'],$order['money'],$order['pay_money'],$order['pay_channel'],$order['buy_remark'],$order['tel'],$order['charge_qq'],$order['service_id'],time());
        $this->err_log(var_export($this->params,1),'qb_order');
        $this->doExecute();

        //更新客服信息
        $this->sql = "update `admins` set last_service_time=? where id=?";
        $this->params = array(strtotime("now"), $order['service_id']);
        $this->doExecute();
    }
}